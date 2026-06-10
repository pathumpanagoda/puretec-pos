<?php

/**
 * Default categories and subcategories for new tenants.
 * These are automatically created when a new business is registered.
 */

return [
    'categories' => [
        [
            'name' => 'Electronics',
            'code' => 'ELEC',
            'color' => '#3498db',
            'icon' => 'bi-cpu',
            'children' => [
                ['name' => 'Mobile Phones', 'code' => 'ELEC-MOB', 'color' => '#2980b9', 'icon' => 'bi-phone'],
                ['name' => 'Laptops & Computers', 'code' => 'ELEC-LAP', 'color' => '#2980b9', 'icon' => 'bi-laptop'],
                ['name' => 'Tablets', 'code' => 'ELEC-TAB', 'color' => '#2980b9', 'icon' => 'bi-tablet'],
                ['name' => 'Accessories', 'code' => 'ELEC-ACC', 'color' => '#2980b9', 'icon' => 'bi-headphones'],
                ['name' => 'Cameras', 'code' => 'ELEC-CAM', 'color' => '#2980b9', 'icon' => 'bi-camera'],
                ['name' => 'Audio & Video', 'code' => 'ELEC-AV', 'color' => '#2980b9', 'icon' => 'bi-speaker'],
            ],
        ],
        [
            'name' => 'Clothing & Fashion',
            'code' => 'FASH',
            'color' => '#9b59b6',
            'icon' => 'bi-bag',
            'children' => [
                ['name' => 'Men\'s Wear', 'code' => 'FASH-MEN', 'color' => '#8e44ad', 'icon' => 'bi-person'],
                ['name' => 'Women\'s Wear', 'code' => 'FASH-WOM', 'color' => '#8e44ad', 'icon' => 'bi-person-dress'],
                ['name' => 'Kids\' Wear', 'code' => 'FASH-KID', 'color' => '#8e44ad', 'icon' => 'bi-emoji-smile'],
                ['name' => 'Footwear', 'code' => 'FASH-FOT', 'color' => '#8e44ad', 'icon' => 'bi-boot'],
                ['name' => 'Bags & Wallets', 'code' => 'FASH-BAG', 'color' => '#8e44ad', 'icon' => 'bi-handbag'],
                ['name' => 'Jewelry & Watches', 'code' => 'FASH-JWL', 'color' => '#8e44ad', 'icon' => 'bi-watch'],
            ],
        ],
        [
            'name' => 'Food & Beverages',
            'code' => 'FOOD',
            'color' => '#e67e22',
            'icon' => 'bi-cup-straw',
            'children' => [
                ['name' => 'Beverages', 'code' => 'FOOD-BEV', 'color' => '#d35400', 'icon' => 'bi-cup'],
                ['name' => 'Snacks', 'code' => 'FOOD-SNK', 'color' => '#d35400', 'icon' => 'bi-cookie'],
                ['name' => 'Dairy Products', 'code' => 'FOOD-DRY', 'color' => '#d35400', 'icon' => 'bi-egg'],
                ['name' => 'Canned Foods', 'code' => 'FOOD-CAN', 'color' => '#d35400', 'icon' => 'bi-archive'],
                ['name' => 'Fresh Produce', 'code' => 'FOOD-FRS', 'color' => '#d35400', 'icon' => 'bi-tree'],
                ['name' => 'Bakery', 'code' => 'FOOD-BAK', 'color' => '#d35400', 'icon' => 'bi-basket'],
            ],
        ],
        [
            'name' => 'Health & Beauty',
            'code' => 'HLTH',
            'color' => '#e74c3c',
            'icon' => 'bi-heart-pulse',
            'children' => [
                ['name' => 'Medicines', 'code' => 'HLTH-MED', 'color' => '#c0392b', 'icon' => 'bi-capsule'],
                ['name' => 'Skincare', 'code' => 'HLTH-SKN', 'color' => '#c0392b', 'icon' => 'bi-droplet'],
                ['name' => 'Hair Care', 'code' => 'HLTH-HAR', 'color' => '#c0392b', 'icon' => 'bi-scissors'],
                ['name' => 'Personal Care', 'code' => 'HLTH-PRS', 'color' => '#c0392b', 'icon' => 'bi-person-check'],
                ['name' => 'Cosmetics', 'code' => 'HLTH-COS', 'color' => '#c0392b', 'icon' => 'bi-palette'],
                ['name' => 'Vitamins & Supplements', 'code' => 'HLTH-VIT', 'color' => '#c0392b', 'icon' => 'bi-shield-plus'],
            ],
        ],
        [
            'name' => 'Home & Living',
            'code' => 'HOME',
            'color' => '#27ae60',
            'icon' => 'bi-house',
            'children' => [
                ['name' => 'Furniture', 'code' => 'HOME-FUR', 'color' => '#229954', 'icon' => 'bi-lamp'],
                ['name' => 'Kitchen & Dining', 'code' => 'HOME-KIT', 'color' => '#229954', 'icon' => 'bi-cup-hot'],
                ['name' => 'Bedding', 'code' => 'HOME-BED', 'color' => '#229954', 'icon' => 'bi-moon'],
                ['name' => 'Bathroom', 'code' => 'HOME-BTH', 'color' => '#229954', 'icon' => 'bi-droplet-half'],
                ['name' => 'Decor', 'code' => 'HOME-DEC', 'color' => '#229954', 'icon' => 'bi-flower1'],
                ['name' => 'Cleaning Supplies', 'code' => 'HOME-CLN', 'color' => '#229954', 'icon' => 'bi-spray'],
            ],
        ],
        [
            'name' => 'Sports & Outdoors',
            'code' => 'SPRT',
            'color' => '#1abc9c',
            'icon' => 'bi-trophy',
            'children' => [
                ['name' => 'Fitness Equipment', 'code' => 'SPRT-FIT', 'color' => '#16a085', 'icon' => 'bi-bicycle'],
                ['name' => 'Sports Gear', 'code' => 'SPRT-GER', 'color' => '#16a085', 'icon' => 'bi-dribbble'],
                ['name' => 'Outdoor & Camping', 'code' => 'SPRT-OUT', 'color' => '#16a085', 'icon' => 'bi-tree'],
                ['name' => 'Sportswear', 'code' => 'SPRT-WER', 'color' => '#16a085', 'icon' => 'bi-person-walking'],
                ['name' => 'Water Sports', 'code' => 'SPRT-WTR', 'color' => '#16a085', 'icon' => 'bi-water'],
            ],
        ],
        [
            'name' => 'Books & Stationery',
            'code' => 'BOOK',
            'color' => '#795548',
            'icon' => 'bi-book',
            'children' => [
                ['name' => 'Books', 'code' => 'BOOK-BKS', 'color' => '#5d4037', 'icon' => 'bi-journal'],
                ['name' => 'Notebooks & Pads', 'code' => 'BOOK-NTB', 'color' => '#5d4037', 'icon' => 'bi-journal-text'],
                ['name' => 'Pens & Pencils', 'code' => 'BOOK-PEN', 'color' => '#5d4037', 'icon' => 'bi-pen'],
                ['name' => 'Art Supplies', 'code' => 'BOOK-ART', 'color' => '#5d4037', 'icon' => 'bi-brush'],
                ['name' => 'Office Supplies', 'code' => 'BOOK-OFC', 'color' => '#5d4037', 'icon' => 'bi-paperclip'],
            ],
        ],
        [
            'name' => 'Toys & Games',
            'code' => 'TOYS',
            'color' => '#ff5722',
            'icon' => 'bi-controller',
            'children' => [
                ['name' => 'Action Figures', 'code' => 'TOYS-ACT', 'color' => '#e64a19', 'icon' => 'bi-robot'],
                ['name' => 'Board Games', 'code' => 'TOYS-BRD', 'color' => '#e64a19', 'icon' => 'bi-dice-5'],
                ['name' => 'Puzzles', 'code' => 'TOYS-PZL', 'color' => '#e64a19', 'icon' => 'bi-puzzle'],
                ['name' => 'Educational Toys', 'code' => 'TOYS-EDU', 'color' => '#e64a19', 'icon' => 'bi-lightbulb'],
                ['name' => 'Video Games', 'code' => 'TOYS-VID', 'color' => '#e64a19', 'icon' => 'bi-joystick'],
            ],
        ],
        [
            'name' => 'Automotive',
            'code' => 'AUTO',
            'color' => '#607d8b',
            'icon' => 'bi-car-front',
            'children' => [
                ['name' => 'Car Accessories', 'code' => 'AUTO-CAR', 'color' => '#546e7a', 'icon' => 'bi-car-front-fill'],
                ['name' => 'Bike Accessories', 'code' => 'AUTO-BIK', 'color' => '#546e7a', 'icon' => 'bi-bicycle'],
                ['name' => 'Tools & Equipment', 'code' => 'AUTO-TOL', 'color' => '#546e7a', 'icon' => 'bi-tools'],
                ['name' => 'Oils & Fluids', 'code' => 'AUTO-OIL', 'color' => '#546e7a', 'icon' => 'bi-droplet-fill'],
                ['name' => 'Parts & Spares', 'code' => 'AUTO-PRT', 'color' => '#546e7a', 'icon' => 'bi-gear'],
            ],
        ],
        [
            'name' => 'Pet Supplies',
            'code' => 'PETS',
            'color' => '#8bc34a',
            'icon' => 'bi-piggy-bank',
            'children' => [
                ['name' => 'Pet Food', 'code' => 'PETS-FOD', 'color' => '#7cb342', 'icon' => 'bi-egg-fried'],
                ['name' => 'Pet Accessories', 'code' => 'PETS-ACC', 'color' => '#7cb342', 'icon' => 'bi-house-heart'],
                ['name' => 'Pet Health', 'code' => 'PETS-HLT', 'color' => '#7cb342', 'icon' => 'bi-heart'],
                ['name' => 'Pet Toys', 'code' => 'PETS-TOY', 'color' => '#7cb342', 'icon' => 'bi-balloon'],
            ],
        ],
        [
            'name' => 'Services',
            'code' => 'SERV',
            'color' => '#00bcd4',
            'icon' => 'bi-person-workspace',
            'children' => [
                ['name' => 'Repairs', 'code' => 'SERV-REP', 'color' => '#00acc1', 'icon' => 'bi-wrench'],
                ['name' => 'Installation', 'code' => 'SERV-INS', 'color' => '#00acc1', 'icon' => 'bi-hammer'],
                ['name' => 'Maintenance', 'code' => 'SERV-MNT', 'color' => '#00acc1', 'icon' => 'bi-gear-wide-connected'],
                ['name' => 'Consultation', 'code' => 'SERV-CON', 'color' => '#00acc1', 'icon' => 'bi-chat-dots'],
            ],
        ],
        [
            'name' => 'Others',
            'code' => 'OTHR',
            'color' => '#9e9e9e',
            'icon' => 'bi-three-dots',
            'children' => [
                ['name' => 'Gift Cards', 'code' => 'OTHR-GFT', 'color' => '#757575', 'icon' => 'bi-gift'],
                ['name' => 'Miscellaneous', 'code' => 'OTHR-MSC', 'color' => '#757575', 'icon' => 'bi-box'],
            ],
        ],
    ],

    // Default expense categories for new tenants
    'expense_categories' => [
        'Rent',
        'Utilities',
        'Salaries',
        'Maintenance',
        'Marketing',
        'Transportation',
        'Supplies',
        'Insurance',
        'Taxes',
        'Miscellaneous',
    ],

    // Default customer groups for new tenants
    'customer_groups' => [
        ['name' => 'General', 'discount_rate' => 0, 'min_purchase' => 0],
        ['name' => 'Wholesale', 'discount_rate' => 10, 'min_purchase' => 50000],
        ['name' => 'VIP', 'discount_rate' => 15, 'min_purchase' => 0],
        ['name' => 'Corporate', 'discount_rate' => 12, 'min_purchase' => 100000],
    ],

    // Default taxes for new tenants
    'taxes' => [
        ['name' => 'VAT 15%', 'rate' => 15, 'type' => 'percentage', 'is_inclusive' => false],
        ['name' => 'Service Tax 10%', 'rate' => 10, 'type' => 'percentage', 'is_inclusive' => false],
        ['name' => 'No Tax', 'rate' => 0, 'type' => 'percentage', 'is_inclusive' => false],
    ],
];
