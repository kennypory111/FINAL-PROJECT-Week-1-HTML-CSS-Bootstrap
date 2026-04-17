<?php
$teams = [
    "Manchester United" => 100,
    "Real Madrid" => 100,
    "Bayern Munich" => 100,
];

$dbHost = "127.0.0.1";
$dbName = "footyshirts_db";
$dbUser = "root";
$dbPass = "";

$sizes = ["S", "M", "L", "XL"];
$selectedTeam = $_POST["team"] ?? "Real Madrid";
$selectedSize = $_POST["size"] ?? "M";
$customerName = trim($_POST["customer_name"] ?? "");
$email = trim($_POST["email"] ?? "");
$quantity = isset($_POST["quantity"]) ? (int) $_POST["quantity"] : 1;
$delivery = $_POST["delivery"] ?? "standard";
$errors = [];
$successMessage = "";
$orderSummary = null;
$databaseMessage = "";

$deliveryPrices = [
    "standard" => 5,
    "express" => 12,
];

function saveOrderToDatabase(
    string $dbHost,
    string $dbName,
    string $dbUser,
    string $dbPass,
    string $customerName,
    string $email,
    string $selectedTeam,
    string $selectedSize,
    int $quantity,
    string $delivery,
    float $subtotal,
    float $deliveryPrice,
    float $total
): string {
    try {
        $pdo = new PDO(
            "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
            $dbUser,
            $dbPass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        $statement = $pdo->prepare(
            "INSERT INTO orders
            (customer_name, email, team, shirt_size, quantity, delivery_method, subtotal, delivery_price, total_price)
            VALUES
            (:customer_name, :email, :team, :shirt_size, :quantity, :delivery_method, :subtotal, :delivery_price, :total_price)"
        );

        $statement->execute([
            ":customer_name" => $customerName,
            ":email" => $email,
            ":team" => $selectedTeam,
            ":shirt_size" => $selectedSize,
            ":quantity" => $quantity,
            ":delivery_method" => $delivery,
            ":subtotal" => $subtotal,
            ":delivery_price" => $deliveryPrice,
            ":total_price" => $total,
        ]);

        return "Order saved to database successfully.";
    } catch (PDOException $exception) {
        return "Database not connected yet: " . $exception->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if ($customerName === "") {
        $errors[] = "Please enter your name.";
    }

    if ($email === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (!array_key_exists($selectedTeam, $teams)) {
        $errors[] = "Please choose a valid shirt.";
    }

    if (!in_array($selectedSize, $sizes, true)) {
        $errors[] = "Please choose a valid size.";
    }

    if ($quantity < 1 || $quantity > 10) {
        $errors[] = "Quantity must be between 1 and 10.";
    }

    if (!array_key_exists($delivery, $deliveryPrices)) {
        $errors[] = "Please choose a valid delivery method.";
    }

    if (!$errors) {
        $shirtPrice = $teams[$selectedTeam];
        $deliveryPrice = $deliveryPrices[$delivery];
        $subtotal = $shirtPrice * $quantity;
        $total = $subtotal + $deliveryPrice;

        $orderSummary = [
            "name" => htmlspecialchars($customerName, ENT_QUOTES, "UTF-8"),
            "email" => htmlspecialchars($email, ENT_QUOTES, "UTF-8"),
            "team" => htmlspecialchars($selectedTeam, ENT_QUOTES, "UTF-8"),
            "size" => htmlspecialchars($selectedSize, ENT_QUOTES, "UTF-8"),
            "quantity" => $quantity,
            "delivery" => $delivery === "express" ? "Express Delivery" : "Standard Delivery",
            "subtotal" => number_format($subtotal, 2),
            "delivery_price" => number_format($deliveryPrice, 2),
            "total" => number_format($total, 2),
        ];

        $databaseMessage = saveOrderToDatabase(
            $dbHost,
            $dbName,
            $dbUser,
            $dbPass,
            $customerName,
            $email,
            $selectedTeam,
            $selectedSize,
            $quantity,
            $delivery,
            $subtotal,
            $deliveryPrice,
            $total
        );

        $successMessage = "Order submitted successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Order Form | FootyShirts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark site-navbar sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.html">FOOTYSHIRTS</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="index.html">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="products.html">All Kits</a></li>
                    <li class="nav-item"><a class="nav-link active" href="order.php">PHP Order</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <header class="page-banner">
        <div class="container">
            <div class="page-banner-copy">
                <p class="eyebrow">week 3 php final</p>
                <h1 class="section-title mb-3">Simple PHP Football Shirt Order Form</h1>
                <p class="text-muted-custom mb-0">This page uses PHP to validate the form, calculate the total price, and save orders into MySQL.</p>
            </div>
        </div>
    </header>

    <main class="section pt-0">
        <div class="container">
            <div class="product-layout">
                <section class="product-summary">
                    <h2 class="h3 fw-bold mb-3">Place an Order</h2>

                    <?php if ($errors): ?>
                        <div class="alert alert-danger" role="alert">
                            <strong>Please fix these issues:</strong>
                            <ul class="mb-0 mt-2">
                                <?php foreach ($errors as $error): ?>
                                    <li><?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <?php if ($successMessage !== ""): ?>
                        <div class="alert alert-success" role="alert">
                            <?= htmlspecialchars($successMessage, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($databaseMessage !== ""): ?>
                        <div class="alert alert-info" role="alert">
                            <?= htmlspecialchars($databaseMessage, ENT_QUOTES, "UTF-8") ?>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="order.php" class="php-form">
                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="customer_name">Full Name</label>
                            <input class="form-control" type="text" id="customer_name" name="customer_name" value="<?= htmlspecialchars($customerName, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="email">Email Address</label>
                            <input class="form-control" type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="team">Choose Shirt</label>
                            <select class="form-select" id="team" name="team">
                                <?php foreach ($teams as $team => $price): ?>
                                    <option value="<?= htmlspecialchars($team, ENT_QUOTES, "UTF-8") ?>" <?= $selectedTeam === $team ? "selected" : "" ?>>
                                        <?= htmlspecialchars($team, ENT_QUOTES, "UTF-8") ?> - &pound;<?= number_format($price, 2) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="size">Size</label>
                            <select class="form-select" id="size" name="size">
                                <?php foreach ($sizes as $size): ?>
                                    <option value="<?= $size ?>" <?= $selectedSize === $size ? "selected" : "" ?>><?= $size ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="quantity">Quantity</label>
                            <input class="form-control" type="number" id="quantity" name="quantity" min="1" max="10" value="<?= htmlspecialchars((string) $quantity, ENT_QUOTES, "UTF-8") ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-block">Delivery Method</label>
                            <div class="delivery-options">
                                <label class="delivery-card">
                                    <input type="radio" name="delivery" value="standard" <?= $delivery === "standard" ? "checked" : "" ?>>
                                    <span>Standard Delivery - &pound;5.00</span>
                                </label>
                                <label class="delivery-card">
                                    <input type="radio" name="delivery" value="express" <?= $delivery === "express" ? "checked" : "" ?>>
                                    <span>Express Delivery - &pound;12.00</span>
                                </label>
                            </div>
                        </div>

                        <div class="product-actions">
                            <button type="submit" class="btn btn-brand">Submit Order</button>
                            <a href="products.html" class="btn btn-secondary-soft">Back to Products</a>
                        </div>
                    </form>
                </section>

                <section class="product-gallery">
                    <h2 class="h3 fw-bold mb-3">Order Summary</h2>
                    <p class="text-muted-custom">Your PHP result will appear here after you submit the form.</p>

                    <?php if ($orderSummary): ?>
                        <div class="detail-list">
                            <span><strong>Name:</strong> <?= $orderSummary["name"] ?></span>
                            <span><strong>Email:</strong> <?= $orderSummary["email"] ?></span>
                            <span><strong>Shirt:</strong> <?= $orderSummary["team"] ?></span>
                            <span><strong>Size:</strong> <?= $orderSummary["size"] ?></span>
                            <span><strong>Quantity:</strong> <?= $orderSummary["quantity"] ?></span>
                            <span><strong>Delivery:</strong> <?= $orderSummary["delivery"] ?></span>
                            <span><strong>Subtotal:</strong> &pound;<?= $orderSummary["subtotal"] ?></span>
                            <span><strong>Delivery Fee:</strong> &pound;<?= $orderSummary["delivery_price"] ?></span>
                            <span><strong>Total:</strong> &pound;<?= $orderSummary["total"] ?></span>
                        </div>
                    <?php else: ?>
                        <div class="detail-list">
                            <span>Choose a shirt, size, quantity, and delivery option.</span>
                            <span>PHP will validate the form fields.</span>
                            <span>PHP will calculate the total amount automatically.</span>
                            <span>If MySQL is set up, the order will also be saved in the orders table.</span>
                        </div>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container text-center">
            <p class="mb-2"><strong>FootyShirts</strong> | Final PHP submission.</p>
            <div class="footer-links mb-3">
                <a href="index.html">Home</a>
                <a href="products.html">All Kits</a>
                <a href="order.php">PHP Order</a>
            </div>
            <p class="mb-0">&copy; 2026 FootyShirts.</p>
        </div>
    </footer>
</body>
</html>
