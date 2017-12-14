<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>WPML Category and Tag Migration Assistant | Welcome</title>
  <style>
    div#content {
      width:600px;
      margin-left:auto;
      margin-right:auto;
    }
    table {
      width:500px;
      margin-left: auto;
      margin-right: auto;
    }
    td.header {
      font-weight: bold;
      background-color: #ccc;
    }
    td {
      background-color: #e7e7e7;
    }
  </style>
</head>
<body>
  <div id="content">
    <h1>WPML Category and Tag Migration Assistant</h1>
    This utility assists with completing <b>missing post categories</b> and <b>tags</b> (<i>taxonomies</i>) within <a target="_blank" href="https://www.wordpress.org/">WordPress</a> after migrating from <a target="_blank" href="https://wordpress.org/plugins/qtranslate/">qTranslate</a> or <a target="_blank" href="https://wordpress.org/plugins/qtranslate-x/">qTranslate-X</a> to <a target="_blank" href="https://wpml.org/">WPML</a> (<i>WordPress Multilingual Plugin</i>).<br />

    <h2>Prerequisites</h2>
    <p>
      Make sure to install the <a target="_blank" href="https://wordpress.org/plugins/qtranslate-to-wpml-export/">qTranslate X Cleanup and WPML Import</a> plugin within WordPress and consult the following instruction in order to import qTranslate/qTranslate-X translations: <a target="_blank" href="https://wpml.org/documentation/related-projects/qtranslate-importer/">https://wpml.org/documentation/related-projects/qtranslate-importer/</a>.<br />
      I also assume that you already translated all taxonomies (<i>categories and tags</i>) names and followed this naming schema <b>slugName-languageShortcode</b> (<i>2 letter <a target="_blank" href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">ISO 639-1 code</a></i>).</p>
    <p>
      <u>Example:</u><br />
      German slug: <b>linux</b><br />
      English slug: <b>linux-<u>en</u></b><br />
    </p>
    <p style="text-align:center;">
      <img src="translation.jpg" alt="Translation example">
    </p>

    <h2>How it works</h2>
    This utility basically does the following:
    <ol>
      <li>It iterates through all posts of the given source language and discovers associated categories and tags and translations</li>
      <li>In addition, it tries to find translations of linked taxonomies - this requires that you already translated all taxonomies and followed the postfix <b>slugName-languageShortcode</b>.</li>
      <li>Afterwards it maps detected translations to translated taxonomies and increases counters</li>
    </ol>
    The progress is displayed in a table - so you can check-out and verify translated information. If everything is finished, you should see outputs like "<i>Already in category #xxx</i>" and "<i>Already has tag #xxx</i>" next to every translated post.

    <h2>Get started</h2>
    <p>
      <u>Disclaimer:</u> I also assume that you have created a <b>valid backup</b> of your WordPress database. This is an ugly script (<i>you really don't want to see the source code</i>) I wrote in a hurry just to get shit done - so things might go wrong on your installation. So - don't blame me for being dumb: you have been warned. 🤷<br /><br />
    </p>
    <p style="text-align:center;">
      <img src="databaenerys.jpg" alt="Mimimi, my database is broken!" />
    </p>
    <form action="migration.php" method="post">
      <table>
        <tr>
          <td id="header">Database server:</td>
          <td><input type="text" name="database_server" value="localhost"></td>
        </tr>
        <tr>
          <td id="header">Database name:</td>
          <td><input type="text" name="database_name"></td>
        </tr>
        <tr>
          <td id="header">Username:</td>
          <td><input type="text" name="username"></td>
        </tr>
        <tr>
          <td id="header">Password:</td>
          <td><input type="password" name="password"></td>
        </tr>
        <tr>
          <td id="header">Source language (<i>2 letter <a target="_blank" href="https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes">ISO 639-1 code</a></i>):</td>
          <td><input type="text" name="source_lang" value="de"></td>
        </tr>
        <tr>
          <td id="header">Target language (<i>see above</i>):</td>
          <td><input type="text" name="target_lang" value="en"></td>
        </tr>
        <tr>
          <td id="header">Only simulate:</td>
          <td><select name="simulation" size="1"><option value="true" selected>Yes</option><option value="false">No</option></select></td>
        </tr>
        <tr>
          <td colspan="2"><u>Note:</u> For installations with dozens of posts (<i>and shitty web servers</i> 🙄) it might be necessary to run the utility twice.</td>
        </tr>
        <tr>
          <td colspan="2" style="text-align:right;"><input type="submit" value="🔓 and 🔫"></td>
        </tr>
      </table>
    </form>
  </div>
</body>
</html>
