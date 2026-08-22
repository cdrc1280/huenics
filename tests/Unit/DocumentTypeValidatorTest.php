<?php

namespace Tests\Unit;

use App\Models\Document;
use App\Services\DocumentParsers\DocumentTypeValidator;
use InvalidArgumentException;
use Tests\TestCase;

class DocumentTypeValidatorTest extends TestCase
{
    protected DocumentTypeValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->validator = new DocumentTypeValidator();
    }

    public function test_validates_quotation_reference_1_correctly(): void
    {
        $text = <<<OCR
VENDORS AGREEMENT FORM
Quotation No. 25100163 - P rev.2 Date 01/05/25 Customer Name Engr. Ronald Rey Sandoval Company MGS CONSTRUCTION, INC.
Address 2F Starmall Annex, Alabang-Zapote Road, corner Doña Manuela Avenue, Pamplona III, Las Pinas, For Project Palanza Tower
Project Location Palanza St. corner guirayan st., Dona Imelda, Q.C
Phone No. 0906-144-2553
Item Code Product Description Qty Unit Unit Price Discounted Price Total
HISI - MTL- 6W Magnetic Tracklight 6w 3000k 158 pcs 2,100.00 1,890.00 298,620.00
Total Amount: 969,385.00 Negotiated Amount: 950,000.00
Terms and Conditions Validity 15 days Stock Availability ✔ Stock
How To Claim The Warranty
OCR;

        $detected = $this->validator->detectType($text);
        $this->assertEquals(Document::TYPE_VENDORS_AGREEMENT, $detected);

        // Validation for Quotation should succeed without exception
        $this->validator->validate($text, Document::TYPE_VENDORS_AGREEMENT);

        // Validation for PO should fail
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The uploaded document appears to be a Quotation / Vendors Agreement, not a Purchase Order');
        $this->validator->validate($text, Document::TYPE_PURCHASE_ORDER);
    }

    public function test_validates_quotation_reference_2_correctly(): void
    {
        $text = <<<OCR
VENDORS AGREEMENT FORM
Quotation No. 261001- P Date 01/05/26
Customer Name Engr. Ronald Rey Sandoval
Company MGS CONSTRUCTION, INC.
For Project Palanza Tower Project Location Palanza St. corner Guirayan st., Dona Imelda, Q.C
Item Code Product Description Qty Unit Unit Price Discounted Price Total
HISI-JF-2240-7w Led Downlight C.O.B Citizen Japan 3500k Warmwhite 7w 500 pcs 1,950.00 1,755.00 877,500.00
Total Amount 1,074,060.00
Negotiated Amount: 1,050,000.00
Terms and Conditions
How To Claim The Warranty
OCR;

        $detected = $this->validator->detectType($text);
        $this->assertEquals(Document::TYPE_VENDORS_AGREEMENT, $detected);

        // Valid for Quotation
        $this->validator->validate($text, Document::TYPE_VENDORS_AGREEMENT);
    }

    public function test_validates_purchase_order_reference_3_correctly(): void
    {
        $text = <<<OCR
No. 4010027093
MGS CONSTRUCTION, INC.
2f Starmall Annex, Alabang-Zapote Rd.
PURCHASE ORDER
Vendor: 203974 HUENICS INDUSTRIAL SALES INC. Date: 01/08/2026 Delivery Date: 01/26/2026
Reference PR No.: 1010032090
Deliver To: 3030 Palanza Tower
Item No. Material Code Qty UoM Material Description Unit Cost Total Cost
10 500 PC 7W LED COB Down Light, 3500K 1,730.94 865,470.00
SUBTOTAL 937,500.00 PHP
VAT 112,500.00 PHP
TOTAL 1,050,000.00 PHP
Important Instruction/Covenants:
1 Indicate PO Seller's Invoice and delivery receipt (DR).
OCR;

        $detected = $this->validator->detectType($text);
        $this->assertEquals(Document::TYPE_PURCHASE_ORDER, $detected);

        // Valid for PO
        $this->validator->validate($text, Document::TYPE_PURCHASE_ORDER);

        // Validation for Quotation should fail
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The uploaded document appears to be a Purchase Order (PO), not a Quotation');
        $this->validator->validate($text, Document::TYPE_VENDORS_AGREEMENT);
    }

    public function test_validates_purchase_order_reference_4_correctly(): void
    {
        $text = <<<OCR
No. 4010027092
MGS CONSTRUCTION, INC.
PURCHASE ORDER
Vendor: 203974 HUENICS INDUSTRIAL SALES INC. Date: 01/08/2026 Delivery Date: 01/22/2026
Deliver To: 3030 Palanza Tower
SUBTOTAL 848,214.34 PHP
VAT 101,785.69 PHP
TOTAL 950,000.03 PHP
Important Instruction/Covenants:
OCR;

        $detected = $this->validator->detectType($text);
        $this->assertEquals(Document::TYPE_PURCHASE_ORDER, $detected);

        // Valid for PO
        $this->validator->validate($text, Document::TYPE_PURCHASE_ORDER);
    }

    public function test_rejects_unrecognized_random_document(): void
    {
        $text = "Receipt No 12345 Customer John Doe Paid Cash 500.00 Thank you for shopping with us.";

        $this->assertNull($this->validator->detectType($text));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('could not be verified as a valid Quotation');
        $this->validator->validate($text, Document::TYPE_VENDORS_AGREEMENT);
    }
}
