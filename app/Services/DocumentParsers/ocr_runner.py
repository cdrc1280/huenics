import argparse
import asyncio
import json
import os
import sys
from PIL import Image
import winocr

async def run_ocr(image_path: str):
    if not os.path.exists(image_path):
        return {"success": False, "error": f"File not found: {image_path}", "text": "", "lines": []}

    try:
        img = Image.open(image_path)
        
        # 1. High-fidelity preprocessing (2.5x upscale with LANCZOS + contrast enhancement)
        w, h = img.size
        scale = 2.5 if max(w, h) < 2500 else 1.5
        img_enhanced = img.convert('L').resize((int(w * scale), int(h * scale)), Image.Resampling.LANCZOS)
        from PIL import ImageEnhance
        enhancer = ImageEnhance.Contrast(img_enhanced)
        img_ready = enhancer.enhance(1.8).convert('RGB')

        res = await winocr.recognize_pil(img_ready)

        # 2. Extract words and bounding boxes for 2D spatial layout reconstruction
        words = []
        for line in res.lines:
            for word in line.words:
                rect = word.bounding_rect
                words.append({
                    'text': word.text,
                    'x': rect.x,
                    'y': rect.y,
                    'w': rect.width,
                    'h': rect.height,
                    'y_center': rect.y + (rect.height / 2.0)
                })

        if not words:
            # Fallback to standard line texts
            lines = [l.text.strip() for l in res.lines if l.text.strip()]
            return {
                "success": True,
                "text": "\n".join(lines),
                "lines": lines,
                "engine": "winocr-native"
            }

        # 3. Sort words primarily by vertical coordinate (y_center)
        words.sort(key=lambda w: (w['y_center'], w['x']))

        # 4. Group words into horizontal row clusters
        line_clusters = []
        line_threshold = 18.0 * (scale / 2.5)

        for word in words:
            placed = False
            for cluster in line_clusters:
                avg_y = sum(w['y_center'] for w in cluster) / len(cluster)
                if abs(word['y_center'] - avg_y) <= line_threshold:
                    cluster.append(word)
                    placed = True
                    break
            if not placed:
                line_clusters.append([word])

        # 5. Sort line clusters vertically by average Y position
        line_clusters.sort(key=lambda cluster: sum(w['y_center'] for w in cluster) / len(cluster))

        # 6. Reconstruct each horizontal line from left to right with proportional spacing
        reconstructed_lines = []
        for cluster in line_clusters:
            cluster.sort(key=lambda w: w['x'])
            line_text = ""
            last_x_end = None
            for w in cluster:
                if last_x_end is not None:
                    gap = w['x'] - last_x_end
                    if gap > 35 * (scale / 2.5):
                        line_text += "    "
                    elif gap > 12 * (scale / 2.5):
                        line_text += "  "
                    else:
                        line_text += " "
                line_text += w['text']
                last_x_end = w['x'] + w['w']
            
            line_clean = line_text.strip()
            if line_clean:
                reconstructed_lines.append(line_clean)

        full_text = "\n".join(reconstructed_lines)
        return {
            "success": True,
            "text": full_text,
            "lines": reconstructed_lines,
            "engine": "winocr-native"
        }
    except Exception as e:
        return {
            "success": False,
            "error": str(e),
            "text": "",
            "lines": [],
            "engine": "winocr-failed"
        }

def main():
    parser = argparse.ArgumentParser()
    parser.add_argument("--image", required=True, help="Path to image file")
    args = parser.parse_args()

    result = asyncio.run(run_ocr(args.image))
    print(json.dumps(result))

if __name__ == "__main__":
    main()
