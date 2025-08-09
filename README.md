# RA Inventory Synchronizer 
Author: Roberto Aleman, ventics.com

A powerful PHP class designed to synchronize and audit inventories from CSV files. It uses the **SKU** as a unique identifier to merge data, consolidate stock, and generate a detailed report of the discrepancies and status of each product. 

## Features 

- **Inventory Synchronization:** Combines two inventory files based on a reference SKU. 
- **Stock Consolidation:** Automatically sums the stock of duplicate products. 
- **Inconsistency Detection:** Compares key fields and reports on data discrepancies between inventories. 
- **Detailed Report:** Generates an output CSV file with additional columns (`status` and `notes`) that explain the result of the synchronization. 
- **Flexibility:** Allows you to specify which columns should be ignored during discrepancy detection. 

## How to Use 

### 1. Input Files 

The class expects two input CSV files (`.csv`) with a header containing the column names. An example of the file structure could be: 

**`inventory1.csv`** 
```csv 
sku,name,stock,location,price 
PRD-001,24" Monitor,10,Warehouse A,200 
PRD-002,USB Keyboard,25,Warehouse B,50 
PRD-003,Gaming Mouse,5,Warehouse A,30


Parameter 		Type 		Description
$inventoryFile1		Path string 	The path to the first inventory CSV file.
$inventoryFile2		Path string 	The path to the second inventory CSV file.
$skuColumnName 		string 		The name of the column containing the SKU (e.g., 'sku'). This is the unique identifier.
$stockColumnName 	string 		The name of the column containing the stock quantities (e.g., 'stock').
$columnsToIgnore 	array 		An array of column names that should not be considered when detecting data differences between duplicate products.
$outputFilePath 	string 		The full path where the consolidated output CSV file will be saved.


The consolidated_inventory_with_report.csv output file will have the following structure, with two new columns for the report:

Column 			Description
sku, 	name, 	etc. 	The original inventory columns.
status 			Indicates the product status (Exists in Inventory 1, New in Inventory 2, Duplicate Found).
notes 			Provides detailed observations, such as differences in data for duplicate SKUs.
