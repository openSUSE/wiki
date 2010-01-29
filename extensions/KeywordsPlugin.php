<?php
   # Example WikiMedia extension
   # with WikiMedia's extension mechanism it is possible to define
   # new tags of the form
   # <TAGNAME> some text </TAGNAME>
   # the function registered by the extension gets the text between the
   # tags as input and can transform it into arbitrary HTML code.
   # Note: The output is not interpreted as WikiText but directly
   #       included in the HTML output. So Wiki markup is not supported.
   # To activate the extension, include it from your LocalSettings.php
   # with: include("extensions/YourExtensionName.php");

   $wgExtensionFunctions[] = "wfKeywordExtension";

   function wfKeywordExtension() {
      global $wgParser, $wgCanonicalNamespaceNames;
      # register the extension with the WikiText parser
      # the first parameter is the name of the new tag.
      # In this case it defines the tag <example> ... </example>
      # the second parameter is the callback function for
      # processing the text between the tags
      $wgParser->setHook( "keyword", "renderKeyword" );
   }


   # The callback function for converting the input text to HTML output
   function renderKeyword( $input, $argv ) {
      global $wgOut, $wgTitle;
      # $argv is an array containing any arguments passed to the
      # extension ike <example argument="foo" bar>..
      $keywords = preg_split('/\s*,\s*/', trim($input));
      $articleID = $wgTitle->getArticleID();
      $titleText = $wgTitle->getText();

      foreach ($keywords as $keyword) {
         $rows[] = array(
            'kl_to'      => $keyword,
            'kl_from'    => $articleID,
            'kl_sortkey' => $titleText
         );
      };
      $output = implode ( ' | ', $keywords);
      return "<div id='catlinks'><p class='catlinks'><strong>Keywords:</strong> {$output}</p></div>";
   }
?>
