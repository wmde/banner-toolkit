# WMDE banner uploader

This command line tool is for uploading campaign banners to a wiki.
  
## Installation
  
Install the command line tool as a global command  
  
    composer install -g wmde/bannerworkflow
    
## Usage

### Concepts and conventions

Banner names in the wiki should follow the Schema `<CAMPAIGN>_<TEST_NAME>_<VARIANT>`. 
Example name: `C16WMDE_01_161224_ctrl`

 * `<CAMPAIGN>` is the general campaign prefix, common to all banners of a campaign.  
   Example: `C16WMDE` `C15WMDE_mobile_`, etc  
 * `<TEST_NAME>` is the test number and the approximate date (in YYMMDD format) the test was created/went live.  
   Example: `01_161112`, `10_16123024`
 * `<VARIANT>` are the different variants of a banner, usually `ctrl` and `var`  
  
### Create credentials and campaign configuration
Copy the files from the `config-examples` directory to the files `.env` and `.campaign_config`. Put the files in the directory where you will edit and upload the banners.

### Uploading banners

**Attention:** If you're using CentralNotice, you must first create one or more banners in CentralNotice. This will create the banner text as wiki pages in the `MediaWiki` namespace. *It does not work the other way around!* 

Create banner HTML files named after variants, e.g. `Banner_ctrl.html` and `Banner_var.html`.

Call the command like this (changing the placeholders):
  
    banner_workflow upload <TEST_NAME>
    
The command will then look for HTML files matching the `Banner_<VARIANT>.html` pattern and copy the file contents to the corresponding page (putting toghether the page name from the configuration, the test name and the variant parts in the file names).

## Why use this tool instead of CentralNotice editor?

On CentralNotice you only have a textarea for entering banner code. That means you lose focus when you save, have no line numbers and syntax highlighting, no indentation tools except your spacebar.

Furthermore, this tool will probably expanded with cool stuff to further modularize the banner code and re-assemble it before uploading. 
