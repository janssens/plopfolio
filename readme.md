╱╱╱╭╮╱╱╱╱╱╱╱╭━╮╱╱╭╮
╱╱╱┃┃╱╱╱╱╱╱╱┃╭╯╱╱┃┃
╭━━┫┃╭━━┳━━┳╯╰┳━━┫┃╭┳━━╮
┃╭╮┃┃┃╭╮┃╭╮┣╮╭┫╭╮┃┃┣┫╭╮┃
┃╰╯┃╰┫╰╯┃╰╯┃┃┃┃╰╯┃╰┫┃╰╯┃
┃╭━┻━┻━━┫╭━╯╰╯╰━━┻━┻┻━━╯
┃┃╱╱╱╱╱╱┃┃
╰╯╱╱╱╱╱╱╰╯

##Plugin Description:

**Plopfolio // Easy portfolio for [getsimple](http://get-simple.info/)**

You can create a portfolio (collection of images) with titles, descriptions, links, keywords and thumbnails.

You can choose how to present the images and the data associated to the visitors by include them into your template using php.

###In use exemples:

- [amandinelle.fr](http://amandinelle.fr)
- [plopcom.fr](http://plopcom.fr)

##Install Instructions:

###How to start in a few steps:

1. Download Plopfolio and unzip it into your plugins directory.
2. Go to the file manager and create a directory for your images if it does not already exist.
3. Go to the plugin tab and tune the plugin as you wish, dont forget to specify the directory.
4. Go to the plopfolio tab, and start creating your first entry.
5. To display all the entries in a page, you can use this snippets

```php
<ul>
   <!--plopfolio_theloop-->
   <li>
      <a class="[plopfolio_entry_keywords_with_space]" href="[plopfolio_entry_img]" title="[plopfolio_entry_month]/[plopfolio_entry_year] [plopfolio_entry_desc]">
        <img alt="[plopfolio_entry_name]" src="[plopfolio_entry_thumb]" />
      </a>
   </li>
   <!--/plopfolio_theloop-->
</ul>
```

**NB**: inside the loop, you can change the html as you wish.

My advise is to find a nice looking gallery or carrousel on the web, and to use plopfolio to generate the code that it need to run. You should for exemple look at:

- [galleria.io](http://galleria.io/)
- [dev7studios.com/nivo-slider/](http://dev7studios.com/nivo-slider/)
- [masonry.desandro.com](http://masonry.desandro.com)

If you have any suggestion contact me.