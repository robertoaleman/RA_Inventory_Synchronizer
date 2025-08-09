<?php

/**
 * RA InventorySynchronizer Class
 *
 * This class synchronizes two inventory files in CSV format.
 * It identifies products by a unique SKU, compares their stock levels,
 * and generates a report of any discrepancies.
 *
 * The second inventory file is considered the  source of truth  for the final state.
 */
class InventorySynchronizer
{
    /**
     * @var string Path to the first inventory file.
     */
    private string $filePath1;

    /**
     * @var string Path to the second inventory file (source of truth).
     */
    private string $filePath2;

    /**
     * @var array Holds the parsed data from the first inventory, indexed by SKU.
     */
    private array $inventory1 = [];

    /**
     * @var array Holds the parsed data from the second inventory, indexed by SKU.
     */
    private array $inventory2 = [];

    /**
     * @var array Stores the results of the synchronization analysis.
     */
    private array $report = [];

    /**
     * @var array The final, merged and synchronized inventory.
     */
    private array $synchronizedInventory = [];

    /**
     * Constructor to initialize the synchronizer with file paths.
     *
     * @param string $filePath1 Path to the initial inventory file.
     * @param string $filePath2 Path to the master inventory file.
     */
    public function __construct(string $filePath1, string $filePath2)
    {
        $this->filePath1 = $filePath1;
        $this->filePath2 = $filePath2;
    }

    /**
     * Executes the main synchronization process.
     *
     * @return bool Returns true on success, false on failure.
     * @throws Exception If inventory files cannot be read.
     */
    public function synchronize(): bool
    {
        try {
            $this->loadInventories();
            $this->compareAndMerge();
        } catch (Exception $e) {
            // In a real application, you might use a logging system.
            error_log($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Returns the generated synchronization report.
     *
     * @return array The report detailing stock variations.
     */
    public function getReport(): array
    {
        return $this->report;
    }

    /**
     * Returns the final, synchronized inventory data.
     * The result is based on inventory 2, with new products added and stock levels updated.
     *
     * @return array The complete synchronized inventory.
     */
    public function getSynchronizedInventory(): array
    {
        return $this->synchronizedInventory;
    }

    /**
     * Loads and parses both inventory CSV files into associative arrays.
     *
     * @throws Exception if a file does not exist or is not readable.
     */
    private function loadInventories(): void
    {
        $this->inventory1 = $this->parseInventoryFile($this->filePath1);
        $this->inventory2 = $this->parseInventoryFile($this->filePath2);
    }

    /**
     * Parses a single inventory CSV file.
     *
     * @param string $filePath The path to the CSV file.
     * @return array The parsed data, indexed by SKU.
     * @throws Exception If the file cannot be opened.
     */
    private function parseInventoryFile(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new Exception("Error: File not found or is not readable: " . $filePath);
        }

        $inventory = [];
        if (($handle = fopen($filePath, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Ensure the row has the correct number of columns
                if (count($data) === 4) {
                    $sku = $data[0];
                    $inventory[$sku] = [
                        'sku'   => $sku,
                        'name'  => $data[1],
                        'price' => (float) $data[2],
                        'stock' => (int) $data[3],
                    ];
                }
            }
            fclose($handle);
        } else {
            throw new Exception("Error: Could not open file: " . $filePath);
        }

        return $inventory;
    }

    /**
     * Compares the two inventories and merges them, generating the report.
     */
    private function compareAndMerge(): void
    {
        $reportData = [];
        $finalInventory = $this->inventory2; // Start with inventory 2 as the base.

        // SKUs present in the master file (inv2)
        foreach ($this->inventory2 as $sku => $product2) {
            $oldStock = 0; // Assume 0 if it's a new product
            $status = 'newly_added';

            if (isset($this->inventory1[$sku])) {
                // Product exists in both inventories
                $product1 = $this->inventory1[$sku];
                $oldStock = $product1['stock'];
                $status = 'updated';
            }

            if ($oldStock !== $product2['stock']) {
                $reportData[] = [
                    'sku'       => $sku,
                    'name'      => $product2['name'],
                    'status'    => ($oldStock === 0 && isset($this->inventory1[$sku])) ? 'stock_depleted' : $status,
                    'old_stock' => $oldStock,
                    'new_stock' => $product2['stock'],
                    'variation' => $product2['stock'] - $oldStock,
                ];
            }
        }

        // Check for SKUs that were in inv1 but are NOT in inv2 (discontinued)
        foreach ($this->inventory1 as $sku => $product1) {
            if (!isset($this->inventory2[$sku])) {
                $reportData[] = [
                    'sku'       => $sku,
                    'name'      => $product1['name'],
                    'status'    => 'removed',
                    'old_stock' => $product1['stock'],
                    'new_stock' => 0,
                    'variation' => -$product1['stock'],
                ];
                // This product should not be in the final inventory.
                // Since our $finalInventory is based on inv2, this is already handled.
            }
        }

        $this->report = $reportData;
        $this->synchronizedInventory = $finalInventory;
    }
}