<?php

require_once 'RA_InventorySynchronizer.php';

// --- Step 1: Sample Data ---
// inv1.csv -> Inventory published on the WEBSITE
$web_inventory_content = "SKU-001,Laptop Pro,1200.50,0
SKU-002,Wireless Mouse,25.00,0
SKU-003,USB-C Hub,45.99,0
SKU-004,4K Monitor,350.00,0
SKU-006,Old Keyboard,30.00,0";

// inv2.csv -> Actual inventory in the WAREHOUSE
$warehouse_inventory_content = "SKU-001,Laptop Pro X,1250.00,60
SKU-002,Wireless Mouse,25.00,200
SKU-003,USB-C Hub Advanced,45.99,180
SKU-005,Webcam HD,55.00,100
SKU-006,Old Keyboard,30.00,0";

file_put_contents('inv1.csv', $web_inventory_content);
file_put_contents('inv2.csv', $warehouse_inventory_content);

$web_file = 'inv1.csv';
$warehouse_file = 'inv2.csv';

// We instantiate the class with the correct files
$synchronizer = new InventorySynchronizer($web_file, $warehouse_file);

// Start the HTML document
echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Variance Report</title>
    <style>
        body { font-family: sans-serif; margin: 40px; background-color: #f4f7f6; color: #333; }
        .container { max-width: 1000px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        h1, h2 { color: #2c3e50; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        p { line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin: 25px 0; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        thead th { background-color: #3498db; color: #ffffff; font-weight: bold; text-transform: uppercase; }
        tbody tr:nth-of-type(even) { background-color: #f9f9f9; }
        tbody tr:hover { background-color: #ecf0f1; }
        .status-updated { background-color: #f1c40f; color: #333; }
        .status-newly_added { background-color: #2ecc71; color: #fff; }
        .status-removed { background-color: #e74c3c; color: #fff; }
        .status-stock_depleted { background-color: #d35400; color: #fff; }
        .variation { font-weight: bold; }
        .variation-positive { color: #27ae60; }
        .variation-negative { color: #c0392b; }
    </style>
</head>
<body>
<div class="container">
    <h1>Inventory Variation Report</h1>
    <p>This report compares the published inventory on the <strong>website (inv1.csv)</strong> with the physical inventory in the <strong>warehouse (inv2.csv)</strong>. Variances indicate actions needed to update the online store.</p>
HTML;

if ($synchronizer->synchronize()) {
    $report = $synchronizer->getReport();
    $warehouse_inventory = $synchronizer->getSynchronizedInventory();

    // --- Table 1: Variation Report ---
    echo "<h2>📝 Report of Detected Variations</h2>";
    if (empty($report)) {
        echo "<p>✅ Excellent! No variations found. The inventory on the website matches the inventory in the warehouse.</p>";
    } else {
        echo "<table>";
        echo "<thead><tr><th>SKU</th><th>Product</th><th>Status</th><th>Stock on Web</th><th>Stock in Warehouse</th><th>Variation</th></tr></thead>";
        echo "<tbody>";
        foreach ($report as $item) {
            $variationClass = $item['variation'] > 0 ? 'variation-positive' : ($item['variation'] < 0 ? 'variation-negative' : '');

            // Translate the states for greater clarity in the report
            $status_text = '';
            switch($item['status']) {
                case 'updated': $status_text = 'Needs Update'; break;
                case 'newly_added': $status_text = 'Add to Web,New Stock in Warehouse'; break;
                case 'removed': $status_text = 'Out of stock in Warehouse(Remove from Web)'; break;
                case 'stock_depleted': $status_text = 'Out of stock in web, check stock in Warehouse'; break;
                default: $status_text = $item['status'];
            }

            echo "<tr>";
            echo "<td>{$item['sku']}</td>";
            echo "<td>{$item['name']}</td>";
            echo "<td class='status-{$item['status']}'>{$status_text}</td>";
            echo "<td>{$item['old_stock']}</td>";
            echo "<td>{$item['new_stock']}</td>";
            echo "<td class='variation {$variationClass}'>" . ($item['variation'] > 0 ? '+' : '') . $item['variation'] . "</td>";
            echo "</tr>";
        }
        echo "</tbody></table>";
    }

    // --- Table 2: Actual Warehouse Inventory ---
    echo "<h2>📦 Actual Warehouse Inventory (Source of Truth)</h2>";
    echo "<p>This table shows the current and correct status of all products that physically exist in the warehouse.</p>";
    echo "<table>";
    echo "<thead><tr><th>SKU</th><th>Product</th><th>Price</th><th>Curren Stock</th></tr></thead>";
    echo "<tbody>";
    foreach ($warehouse_inventory as $product) {
        echo "<tr>";
        echo "<td>{$product['sku']}</td>";
        echo "<td>{$product['name']}</td>";
        echo "<td>" . number_format($product['price'], 2) . "</td>";
        echo "<td>{$product['stock']}</td>";
        echo "</tr>";
    }
    echo "</tbody></table>";

} else {
    echo "<h2>❌ Error</h2>";
    echo "<p>An error occurred during processing. Please check that the inventory files exist and are in the correct format.</p>";
}

// Close the HTML document
echo <<<HTML
</div>
</body>
</html>
HTML;
?>