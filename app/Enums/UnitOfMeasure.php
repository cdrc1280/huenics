<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum UnitOfMeasure: string implements HasLabel
{
    case Pcs = 'pcs';
    case Set = 'set';
    case Sets = 'sets';
    case Lot = 'lot';
    case Unit = 'unit';
    case Units = 'units';
    case Box = 'box';
    case Boxes = 'boxes';
    case Roll = 'roll';
    case Rolls = 'rolls';
    case Meter = 'meter';
    case Meters = 'meters';
    case Pack = 'pack';
    case Packs = 'packs';
    case Pair = 'pair';
    case Pairs = 'pairs';
    case Length = 'length';
    case Lengths = 'lengths';
    case Kg = 'kg';
    case Ltr = 'ltr';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Pcs => 'pcs',
            self::Set => 'set',
            self::Sets => 'sets',
            self::Lot => 'lot',
            self::Unit => 'unit',
            self::Units => 'units',
            self::Box => 'box',
            self::Boxes => 'boxes',
            self::Roll => 'roll',
            self::Rolls => 'rolls',
            self::Meter => 'meter',
            self::Meters => 'meters',
            self::Pack => 'pack',
            self::Packs => 'packs',
            self::Pair => 'pair',
            self::Pairs => 'pairs',
            self::Length => 'length',
            self::Lengths => 'lengths',
            self::Kg => 'kg',
            self::Ltr => 'ltr',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }
        return $options;
    }
}
