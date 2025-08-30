# WebForge Project: Architectural Discussion Summary

This document summarizes the key architectural decisions and refinements made during recent discussions for the WebForge static site generator project.

## 1. `webforge.json`: The Single Source of Truth (SSOT)

We have reaffirmed and strengthened the role of `webforge.json` as the central, declarative file containing the entire site's structure, content data, and global configurations. This maintains the benefits of a single, easily version-controlled data source for the entire project.

**Key characteristics of `webforge.json`:**

- **Comprehensive:** Stores all pages, their component instances, component-specific data, global theme settings (colors, typography, breakpoints), navigation structures, and other site-wide data.
- **Declarative:** Describes _what_ the site should be, rather than _how_ it should be built.
- **Scalable:** Analysis confirms that even for large sites (500-2500 pages), the file size (2-10 MB) is manageable and does not pose a significant performance bottleneck for modern PHP interpreters. This validates its use as the SSOT.

## 2. Critical Architectural Change: Introducing a Full-Fledged PHP Development Site

A significant shift has been made from direct static HTML generation to an intermediate phase involving a fully functional PHP development site. This approach offers maximum flexibility, debuggability, and leverages familiar PHP development tools.

**New Process Flow:**
`webforge.json` → `webforge_php_generator.php` → `_dev_site/` (PHP Development Server) → `build.php` → `build/` (Static HTML)

**Details of the new phase:**

### 2.1. `webforge_php_generator.php`

- **Purpose:** This is the new pivotal script. It reads the `webforge.json` file.
- **Output:** Generates a complete, dynamic PHP-based website into a dedicated directory (e.g., `_dev_site/`).
- **`_dev_site/` contents:** Includes:
  - Generated PHP files for each page defined in `webforge.json` (e.g., `index.php`, `about.php`).
  - Copies of the core PHP component files from `components/`.
  - Any PHP layout files (e.g., from a `layouts/` directory, if applicable).
  - Necessary assets (CSS, JS, images) from the development source.
- **Data Handling:** The generated PHP page files and component files within `_dev_site/` are structured to receive data. PHP component files (e.g., `components/header/header.php`) will access their specific instance data via a `$data` variable and global configuration/theme settings via a `$globalConfig` variable.

### 2.2. Development Phase (`_dev_site/`)

- **Environment:** Developers will run a standard PHP server (e.g., Apache, Nginx, or PHP's built-in server) pointing its document root to the `_dev_site/` directory.
- **Benefits:**
  - **Full PHP Debugging:** Allows the use of standard PHP debuggers (e.g., Xdebug), profilers, and IDE features.
  - **Familiar Workflow:** Provides a familiar PHP development experience.
  - **Dynamic Previews:** Pages can be viewed dynamically on the local server before static compilation.

### 2.3. Updated Role of `build.php`

- **Simplified Function:** The `build.php` script's role is significantly streamlined.
- **Process:** It now "crawls" the dynamically generated PHP site within `_dev_site/`, executes each PHP page file, captures its output, and saves it as a static `.html` file in the `build/` directory.
- **Asset Handling:** It also handles the aggregation, minification, and copying of CSS and JavaScript files to `build/`.

## 3. Structural Page Templates for PageBuilder

The concept of "templates" for the PageBuilder has been clarified and refined. These are not merely pre-filled content components but rather **structural "skeletons" of pages**.

- **Purpose:** To define the basic layout of a page, including the arrangement of placeholder components, column counts and ratios, responsiveness options, and potentially the type of menu or page layout to be used. They represent predefined page wireframes.
- **Storage:** These templates will be stored in a dedicated JSON file (e.g., `webforge_page_structure_templates.json`).
- **Template Structure:** Each template object will contain:
  - `id`: A unique identifier (e.g., `"homepage_hero_3col_footer"`).
  - `name`: A human-readable name for the PageBuilder UI.
  - `description`: A brief explanation of the page structure.
  - `category`: A category for filtering (e.g., "Homepage", "Landing Pages").
  - `previewImage`: (Optional) URL to a schematic image of the layout.
  - `templateStructure`: This crucial field will mirror the structure of a `page` object in `webforge.json` (`layout`, `meta`, `components`), but with minimal or placeholder data for its components.
- **PageBuilder Integration:** When a user creates a new page in the PageBuilder, they will select one of these structural templates. The PageBuilder will then copy the `templateStructure` into the new page entry in `webforge.json`, ensuring that all `instanceId` values for components are made unique (e.g., by adding a timestamp or random suffix).
- **Future Potential:** This refined concept lays the groundwork for developing a dedicated `TemplateBuilder` or a "save as template" feature within the editor, empowering users to define and reuse their own page structures.

This architectural evolution aims to provide a robust, flexible, and developer-friendly environment for building highly optimized static websites with WebForge.
