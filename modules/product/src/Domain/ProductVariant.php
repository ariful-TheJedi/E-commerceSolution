<?php

namespace Modules\Product\Domain;

use Modules\Product\Domain\Exceptions\EmptySku;
use Modules\Product\Domain\Exceptions\InvalidCurrency;
use Modules\Product\Domain\Exceptions\InvalidPrice;
use Modules\Product\Domain\Exceptions\InvalidSaleWindow;

/**
 * Sellable SKU for a listing. Not stock, not a price, not an option combo.
 *
 * Feature 2 owns the codes humans and scanners use: sku (required), barcode,
 * gtin, mpn. GTIN is one stored value covering UPC / EAN / ISBN. This module
 * does not generate barcodes.
 *
 * A simple product has exactly one default variant (this object). Extra
 * variants are feature 8. Feature 3 owns integer minor-unit pricing, currency,
 * and an optional sale window. This class does not calculate tax or checkout totals.
 *
 * Pure PHP: no Laravel, no database.
 */
final class ProductVariant
{
    private function __construct(
        private readonly string $id,
        private string $sku,
        private ?string $barcode,
        private ?string $gtin,
        private ?string $mpn,
        private int $priceMinor,
        private ?int $compareAtMinor,
        private ?int $costMinor,
        private string $currency,
        private ?\DateTimeImmutable $saleStartsAt,
        private ?\DateTimeImmutable $saleEndsAt,
        private ?int $weightG,
        private ?int $lengthMm,
        private ?int $widthMm,
        private ?int $heightMm,
        private readonly bool $isDefault,
    ) {
    }

    public static function create(
        string $id,
        string $sku,
        ?string $barcode = null,
        ?string $gtin = null,
        ?string $mpn = null,
        int $priceMinor = 0,
        string $currency = 'XXX',
        ?int $compareAtMinor = null,
        ?int $costMinor = null,
        ?\DateTimeImmutable $saleStartsAt = null,
        ?\DateTimeImmutable $saleEndsAt = null,
        ?int $weightG = null,
        ?int $lengthMm = null,
        ?int $widthMm = null,
        ?int $heightMm = null,
    ): self {
        self::assertPricing($priceMinor, $currency, $compareAtMinor, $costMinor, $saleStartsAt, $saleEndsAt);
        self::assertShipping($weightG, $lengthMm, $widthMm, $heightMm);

        return new self(
            id: $id,
            sku: self::requireSku($sku),
            barcode: self::optional($barcode),
            gtin: self::optional($gtin),
            mpn: self::optional($mpn),
            priceMinor: $priceMinor,
            compareAtMinor: $compareAtMinor,
            costMinor: $costMinor,
            currency: $currency,
            saleStartsAt: $saleStartsAt,
            saleEndsAt: $saleEndsAt,
            weightG: $weightG,
            lengthMm: $lengthMm,
            widthMm: $widthMm,
            heightMm: $heightMm,
            isDefault: true,
        );
    }

    /**
     * Rebuild from a stored row. Does not apply create() defaults.
     */
    public static function reconstitute(
        string $id,
        string $sku,
        ?string $barcode,
        ?string $gtin,
        ?string $mpn,
        bool $isDefault,
        int $priceMinor = 0,
        ?int $compareAtMinor = null,
        ?int $costMinor = null,
        string $currency = 'XXX',
        ?\DateTimeImmutable $saleStartsAt = null,
        ?\DateTimeImmutable $saleEndsAt = null,
        ?int $weightG = null,
        ?int $lengthMm = null,
        ?int $widthMm = null,
        ?int $heightMm = null,
    ): self {
        self::assertPricing($priceMinor, $currency, $compareAtMinor, $costMinor, $saleStartsAt, $saleEndsAt);
        self::assertShipping($weightG, $lengthMm, $widthMm, $heightMm);

        return new self(
            id: $id,
            sku: $sku,
            barcode: $barcode,
            gtin: $gtin,
            mpn: $mpn,
            priceMinor: $priceMinor,
            compareAtMinor: $compareAtMinor,
            costMinor: $costMinor,
            currency: $currency,
            saleStartsAt: $saleStartsAt,
            saleEndsAt: $saleEndsAt,
            weightG: $weightG,
            lengthMm: $lengthMm,
            widthMm: $widthMm,
            heightMm: $heightMm,
            isDefault: $isDefault,
        );
    }

    public function id(): string
    {
        return $this->id;
    }

    public function sku(): string
    {
        return $this->sku;
    }

    public function barcode(): ?string
    {
        return $this->barcode;
    }

    public function gtin(): ?string
    {
        return $this->gtin;
    }

    public function mpn(): ?string
    {
        return $this->mpn;
    }

    public function priceMinor(): int
    {
        return $this->priceMinor;
    }

    public function compareAtMinor(): ?int
    {
        return $this->compareAtMinor;
    }

    public function costMinor(): ?int
    {
        return $this->costMinor;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function saleStartsAt(): ?\DateTimeImmutable
    {
        return $this->saleStartsAt;
    }

    public function saleEndsAt(): ?\DateTimeImmutable
    {
        return $this->saleEndsAt;
    }

    public function weightG(): ?int
    {
        return $this->weightG;
    }

    public function lengthMm(): ?int
    {
        return $this->lengthMm;
    }

    public function widthMm(): ?int
    {
        return $this->widthMm;
    }

    public function heightMm(): ?int
    {
        return $this->heightMm;
    }

    public function changeShipping(
        ?int $weightG,
        ?int $lengthMm,
        ?int $widthMm,
        ?int $heightMm,
    ): void {
        self::assertShipping($weightG, $lengthMm, $widthMm, $heightMm);
        $this->weightG = $weightG;
        $this->lengthMm = $lengthMm;
        $this->widthMm = $widthMm;
        $this->heightMm = $heightMm;
    }

    public function changePricing(
        ?int $priceMinor = null,
        ?int $compareAtMinor = null,
        ?int $costMinor = null,
        ?\DateTimeImmutable $saleStartsAt = null,
        ?\DateTimeImmutable $saleEndsAt = null,
    ): void {
        $nextPrice = $priceMinor ?? $this->priceMinor;

        self::assertPricing($nextPrice, $this->currency, $compareAtMinor, $costMinor, $saleStartsAt, $saleEndsAt);

        $this->priceMinor = $nextPrice;
        $this->compareAtMinor = $compareAtMinor;
        $this->costMinor = $costMinor;
        $this->saleStartsAt = $saleStartsAt;
        $this->saleEndsAt = $saleEndsAt;
    }

    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * Null means leave unchanged. Empty string clears an optional code.
     * SKU cannot be cleared.
     */
    public function changeIdentifiers(
        ?string $sku = null,
        ?string $barcode = null,
        ?string $gtin = null,
        ?string $mpn = null,
    ): void {
        if ($sku !== null) {
            $this->sku = self::requireSku($sku);
        }

        if ($barcode !== null) {
            $this->barcode = self::optional($barcode);
        }

        if ($gtin !== null) {
            $this->gtin = self::optional($gtin);
        }

        if ($mpn !== null) {
            $this->mpn = self::optional($mpn);
        }
    }

    private static function requireSku(string $sku): string
    {
        $sku = trim($sku);

        if ($sku === '') {
            throw new EmptySku();
        }

        return $sku;
    }

    private static function assertShipping(?int $weightG, ?int $lengthMm, ?int $widthMm, ?int $heightMm): void
    {
        foreach ([$weightG, $lengthMm, $widthMm, $heightMm] as $value) {
            if ($value !== null && $value < 0) {
                throw new \InvalidArgumentException('Shipping measurements cannot be negative.');
            }
        }
    }

    private static function optional(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private static function assertPricing(
        int $priceMinor,
        string $currency,
        ?int $compareAtMinor,
        ?int $costMinor,
        ?\DateTimeImmutable $saleStartsAt,
        ?\DateTimeImmutable $saleEndsAt,
    ): void {
        foreach ([$priceMinor, $compareAtMinor, $costMinor] as $amount) {
            if ($amount !== null && $amount < 0) {
                throw new InvalidPrice();
            }
        }

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new InvalidCurrency();
        }

        if (($saleStartsAt === null) !== ($saleEndsAt === null)
            || ($saleStartsAt !== null && $saleEndsAt <= $saleStartsAt)) {
            throw new InvalidSaleWindow();
        }
    }
}
