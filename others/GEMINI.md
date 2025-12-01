# Project Overview

This project is designed for an AI agent to perform image classification. The goal is for the AI to analyze images located in the `visuals/unprocessed` directory remove any smaller duplicates if any are present and categorize them by moving them into the appropriate subfolder within the `visuals/processed` directory.

## Core Task

The central task is to automate the sorting of a digital photo collection. An AI agent will:
1.  Examine the images in the `visuals/unprocessed` folder to see if there are any images that look visually the same, they may be different sizes(by file size) but they class as the same even if they are different sizes(by file size). 
2. Before moving on to 3. remove all but the largest(by file size) of any duplicated images from the folder.
3.  Examine each remaining image in the `visuals/unprocessed` folder.
2.  Understand the content and subject matter of the image.
3.  Determine which of the predefined categories in `visuals/processed` it belongs to.
4.  Look at the image and generate a description of it no more then 250 charecters long no shorter then 50 charecters long, based on what you see. 
5.  Create a new unique name for the image based on what you see of no less then 8 charecters and no more then 45 chareters including  file extension.  eg. "a-black-dog-001.jpg" would be 19 charecters or "a-red-cat.png" would be 14 charecters. replace anyspaces you might have in the file name with dashes.
6.  Rename the image to the new name.
7.  Execute a file operation to create a .txt file with that same new name as the image eg a-red-cat.txt
8. In that new txt file put in three lines.
        Line 1 = New image name
        Line 2 = The image description
        Line 3 = The images final folder location eg. "Animals" or "CloseUp" etc.
9. Save the txt file.  
10.  Move the image and txt files to the correct destination folder.

# Directory Structure

*   `visuals/unprocessed/`: This directory acts as the input queue, holding all the images that need to be classified.

*   `visuals/processed/`: This directory contains the classification categories as subfolders. The AI will move images into one of these folders:
    *   `AI/`
    *   `Animals/`
    *   `Architecture/`
    *   `CloseUp/`
    *   `Documentary/`
    *   `Editorial/`
    *   `Experimental/`
    *   `GraphicDesign/`
    *   `Illustration/`
    *   `Landscapes/`
    *   `Lifestyle/`
    *   `Nature/`
    *   `Portrait/`
    *   `Seascapes/`

## AI Workflow

The intended workflow for the AI agent is as follows:

1.  **Scan and Fingerprint:**
    *   Scan the `visuals/unprocessed` directory for all image files.
    *   For each image, analyze the visual content to identify potential duplicates.

2.  **Duplicate Elimination:**
    *   Compare images visually to find duplicates.
    *   Within each group of duplicates, identify the image with the largest file size.
    *   Delete all other smaller, duplicate images from the `visuals/unprocessed` directory.

3.  **Batch Analysis:**
    *   For all remaining unique images, perform a comprehensive visual analysis to generate the following for each image:
        *   The most suitable category from the subfolders in `visuals/processed`.
        *   A description of the image (between 50 and 250 characters).
        *   A new unique filename (between 8 and 45 characters, using dashes instead of spaces).

4.  **Batch File Operations:**
    *   The AI agent will then generate and execute calls to the `process_image()` shell function for each file.

**`process_image()` Shell Function:**

This function should be defined in your shell environment. It handles moving the file, renaming it, and creating the metadata `.txt` file.

```bash
process_image() {
    local original_path="$1"
    local new_name="$2"
    local category="$3"
    local description="$4"

    local filename=$(basename "$original_path")
    local extension="${filename##*.}"
    local new_filename="${new_name}.${extension}"
    local new_path="/workspaces/visual-analysis/visuals/processed/${category}/${new_filename}"
    local metadata_path="/workspaces/visual-analysis/visuals/processed/${category}/${new_name}.txt"

    # Create category directory if it doesn't exist
    mkdir -p "/workspaces/visual-analysis/visuals/processed/${category}"

    # Move and rename the image
    mv "$original_path" "$new_path"

    # Create the metadata file
    echo "Name: ${new_name}" > "$metadata_path"
    echo "Description: ${description}" >> "$metadata_path"
    echo "Category: ${category}" >> "$metadata_path"
    echo "File: ${new_filename}" >> "$metadata_path"
    echo "Data Added: false" >> "$metadata_path"
}
```

**AI Agent's Role:**

The AI agent will generate and execute calls to `process_image()` for each file. For example:

```bash
process_image "/workspaces/visual-analysis/visuals/unprocessed/cat-photo.jpg" "cat-on-a-sofa" "Animals" "A fluffy cat resting on a red sofa."
```

## Update Visual Data Workflow (PHP Script)

The "update visual data" workflow is now handled by a PHP-based web dashboard.

**To run the dashboard:**

1.  Start a PHP server from the root of the project:
    ```bash
    php -S localhost:8000 -t /workspaces/visual-analysis/dashboard
    ```
2.  Open `http://localhost:8000` in your browser.

**Dashboard Features:**
- Displays the number of processed images in each category.
- Provides a button to "UPDATE VISUAL DATA".

**When the button is clicked:**

1.  An AJAX request is sent to `dashboard/update_visual_data.php`.
2.  The PHP script scans all `.txt` files in `visuals/processed`.
3.  For each file not marked as "Data Added: true", it:
    - Extracts the metadata.
    - Generates the appropriate SQL `INSERT` statements for the `visual_items` and `visual_category_assignments` tables.
    - Appends the SQL to `database/visuals_update.sql`.
    - Updates the `.txt` file to "Data Added: true".
4.  The dashboard provides feedback on the success or failure of the operation.

# Implementation

**The visual processing workflow is executed directly by the AI agent using its native capabilities. No external scripts, tools, or dependencies are required.**

## AI Agent Instructions

### When instructed to "process images":
1. The AI agent will directly execute the workflow described above
2. No Python scripts or external tools will be created or used
3. The AI will report success or failures only
4. The AI will not deviate from the workflow or perform any code testing/checking

### When instructed to "update visual data":
1. The AI agent will directly execute the Update Visual Data Workflow described above  
2. No Python scripts or external tools will be created or used
3. The AI will report success or failures only
4. The AI will not deviate from the workflow or perform any code testing/checking

## Implementation Details

* **Visual Analysis:** AI-powered direct content analysis of images to determine categorization
* **Duplicate Detection:** AI visual comparison to identify similar images regardless of file size differences  
* **File Operations:** Direct file system operations for renaming, moving, and creating metadata files
* **Metadata Generation:** Creates `.txt` files with the required 3-line format (filename, description 50-250 chars, category)
* **Database Integration:** Generates SQL INSERT statements for `visual_items` and `visual_category_assignments` tables
* **Title Formatting:** Converts filenames to proper titles (e.g., "cat-humanoid-spaceship" → "Cat Humanoid Spaceship")
* **URL Construction:** Creates image URLs in format `/visuals/{category}/{filename}`
* **Error Handling:** AI reports any issues encountered during processing
* **Reprocessing Prevention:** Marks files as "Data Added" to avoid duplicate database entries

The AI agent successfully processes images into the predefined categories while maintaining all specified constraints and requirements, and provides seamless database integration capabilities.
