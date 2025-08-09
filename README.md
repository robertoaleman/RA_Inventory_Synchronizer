# RA Inventory Synchronizer 
Author: Roberto Aleman, ventics.com
 

 
Inventory Synchronizer (PHP)
This PHP script is an utility created to compare a website's inventory with the physical stock in a warehouse, both represented by CSV files. When you run it, it generates a detailed HTML report that highlights discrepancies and the necessary actions to keep your data synchronized.

# How It Works
Sample Data Creation: The script begins by creating two example CSV files: inv1.csv (website inventory) and inv2.csv (warehouse inventory). This is for demonstration purposes. In a real-world scenario, you would replace these with your actual data files.

* Synchronization Logic: An external class, InventorySynchronizer.php (not included in this script), handles the core logic. This class is responsible for:

* Reading and parsing data from both CSV files.

* Comparing products using their SKU as a unique identifier.

* Detecting and categorizing variances (e.g., updated stock, new products, removed items).

# HTML Report Generation: 
The script generates a clear and readable 
HTML report to present the findings. 

# The report includes:

* A table listing the detected variances and the status of each product.

* A second table showing the complete warehouse inventory, which is considered the "source of truth."

* CSS styles to visually highlight different statuses and make it easy to identify differences.
