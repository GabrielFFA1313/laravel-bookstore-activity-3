<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;


class BookFactory extends Factory
{
    protected static array $usedIsbns = [];
    
    protected $model = Book::class;

    // ─────────────────────────────────────────────
    // STATIC CACHES — loaded once, reused per record
    // ─────────────────────────────────────────────

    /**
     * Category IDs cached statically so we only hit
     * the database ONCE across all 1M records.
     */
    protected static array $categoryIds = [];

    /**
     * Pre-defined publisher pool — avoids faker overhead
     * and produces realistic, consistent publisher names.
     */
    protected static array $publishers = [
        'Penguin Random House',
        'HarperCollins',
        'Simon & Schuster',
        'Hachette Book Group',
        'Macmillan Publishers',
        'Scholastic',
        'Oxford University Press',
        'Cambridge University Press',
        'Wiley',
        'Springer',
        'Elsevier',
        'McGraw-Hill',
        'Pearson Education',
        'MIT Press',
        'O\'Reilly Media',
    ];

    /**
     * Available book formats — drives pricing logic below.
     */
    protected static array $formats = [
        'hardcover',
        'paperback',
        'ebook',
        'audiobook',
        'large_print',
    ];

    // ─────────────────────────────────────────────
    // CORE DEFINITION
    // ─────────────────────────────────────────────

    public function definition(): array
    {
        // Load category IDs once into static cache
        if (empty(self::$categoryIds)) {
            self::$categoryIds = Category::pluck('id')->toArray();
        }

        $format = $this->faker->randomElement(self::$formats);

        // Format-aware pricing using match expression
        $basePrice = match($format) {
            'hardcover'  => $this->faker->randomFloat(2, 24.99, 89.99),
            'paperback'  => $this->faker->randomFloat(2, 9.99,  29.99),
            'ebook'      => $this->faker->randomFloat(2, 2.99,  19.99),
            'audiobook'  => $this->faker->randomFloat(2, 14.99, 44.99),
            'large_print'=> $this->faker->randomFloat(2, 19.99, 49.99),
            default      => $this->faker->randomFloat(2, 9.99,  49.99),
        };

        return [
            'isbn'           => $this->generateValidIsbn13(),
            'title'          => $this->faker->unique()->sentence(rand(2, 6)),
            'author'         => $this->faker->name(),
            'publisher'      => $this->faker->randomElement(self::$publishers),
            'price'          => $basePrice,
            'stock_quantity' => $this->faker->numberBetween(0, 1000),
            'category_id'    => $this->faker->randomElement(self::$categoryIds),
            'format'         => $format,
            'is_active'      => $this->faker->boolean(85), // 85% chance active
            'created_at'     => now(),
            'updated_at'     => now(),
        ];
    }

    // ─────────────────────────────────────────────
    // FACTORY STATE — Bestseller
    // ─────────────────────────────────────────────

    /**
     * Bestseller state: always active, high stock,
     * premium pricing. Used for seeding featured books.
     *
     * Usage: Book::factory()->bestseller()->count(100)->create();
     */
    public function bestseller(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active'      => true,
            'stock_quantity' => $this->faker->numberBetween(500, 1000),
            'price'          => $this->faker->randomFloat(2, 19.99, 89.99),
        ]);
    }

    // ─────────────────────────────────────────────
    // ISBN-13 GENERATOR
    // ─────────────────────────────────────────────

    /**
     * Generates a valid ISBN-13 with:
     * - 978 prefix (standard book prefix)
     * - 9 random digits
     * - Correct modulo-10 checksum as the final digit
     */
    protected function generateValidIsbn13(): string
{
    // Keep retrying until a unique ISBN is generated
    do {
        $prefix = '978';
        $body   = str_pad(
                    (string)random_int(0, 999999999),
                    9, '0', STR_PAD_LEFT
                  );

        $digits = str_split($prefix . $body);

        $sum = 0;
        foreach ($digits as $i => $digit) {
            $sum += (int)$digit * ($i % 2 === 0 ? 1 : 3);
        }

        $checkDigit = (10 - ($sum % 10)) % 10;
        $isbn = $prefix . $body . $checkDigit;

    } while (static::$usedIsbns[$isbn] ?? false);

    // Mark this ISBN as used
    static::$usedIsbns[$isbn] = true;

    return $isbn;
}
}