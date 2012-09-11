Facetly Prestashop
==================

Install Prestashop Modules
-------------

How to install Facetly Module in Prestashop

1. Before Installing Facetly Module, make sure you already have these requirements:

       a. Any FTP program, such as WinSCP, FileZilla, etc.

       b. Prestashop 1.4.8 or higher (we are not guarantee facetly plugin would be work properly in previous version)
       
       c. Enable curl in php, please follow our guide here (https://www.facetly.com/doc/howto/curl)

2. Download Facetly Module from our site (https://github.com/facetly/facetly_prestashop) and rename folder into facetly then upload it to your module folder using FTP program.

3. Activate Facetly Module in your modules list.

4. After Facetly Module successfully installed in your Prestashop, you will find Facetly Configuration in your modules.

Configure Facetly Module
-------------

Now we are going to configure Facetly Module. There are several steps that you are need to do:

1. Input your Consumer Key, Consumer Secret, Server Name, Search Limit, and Additional Variable in Modules >> Search & Filter >> Facetly Module.

2. Configure your facetly fields. Go to Field Tab and we will see field mapping here. Please follow instruction in (https://www.facetly.com/doc/field) to set field mapping

3. Configure your template. Go to Template tab to set up template for your search page. You will see search template and facet template settings which will be displayed in your search page. You can find more details about template settings in (https://www.facetly.com/doc/template)

4. Reindex data in Reindex tab. This configuration is used to save all data in your store to our server, which will used as your search data. Click Reindex button to start the process. Please note: you should wait until process is complete and not move to other page, otherwise your data reindex will not completed and you must start from the beginning.

5. Set search box and facetly result into top left sidebar. Go to modules >> Position then find left column blocks tab and change position Facetly Module into top of sidebar


