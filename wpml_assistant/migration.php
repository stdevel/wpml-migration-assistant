<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>WPML Category and Tag Migration Assistant | Data Migration</title>
  <style>
    thead td {
      font-weight: bold;
      background-color: #ccc;
    }
    tbody td {
      background-color: #e7e7e7;
    }
    table ul {
      padding:0;
    }
    table ul li {
      list-style-type: none;
    }
  </style>
</head>
<body>
<?php
class cleanup {
  private $server;        //MySQL server
  private $database;      //database name
  private $username;      //username
  private $password;      //password
  private $source_lang;   //source language in ISO-639-1
  private $target_lang;   //target language in ISO-639-1
  private $simulation;    //flag whether changes should only be simulated

  private $conn;          //MySQL connection

  /*changing things beyond this line might kill kitten*/

  //constructor
  public function __construct($server, $database, $username, $password, $source_lang, $target_lang, $simulation) {
    //set parameters
    $this->server = $server;
    $this->database = $database;
    $this->username = $username;
    $this->password = $password;
    $this->source_lang = $source_lang;
    $this->target_lang = $target_lang;
    $this->simulation = $simulation;
    //create connection
    $this->conn = new mysqli(
      $this->server, $this->username, $this->password, $this->database
    );
    //check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error . "<br />");
    }
    echo "Connected successfully<br />";
  }

  function get_data($query) {
    //read from database
    //echo $query;
    $result = $this->conn->query($query);
    if ($result->num_rows > 0) {
      //return result
      return $result->fetch_all(MYSQLI_BOTH);
    }
    return [];
  }

  function set_data($query) {
    //write to database
    //echo $query
    if ($this->conn->query($query) === TRUE) {
      //return flag
      return true;
    }
    return false;
  }

  function get_translation($object_id, $element_type = "post_post") {
    if($element_type == "post_post") {
      //find translation by ID
      $translations = $this->get_data("SELECT `element_id` FROM `wp_icl_translations` WHERE trid = (SELECT trid FROM `wp_icl_translations` WHERE `element_id` = " . $this->conn->real_escape_string($object_id). " AND `element_type` = '" . $this->conn->real_escape_string($element_type) . "') AND `source_language_code` = '" . $this->conn->real_escape_string($this->source_lang) . "' LIMIT 1");
      //return ID if translation found
      if (sizeof($translations) > 0) {
        return $translations[0]['element_id'];
      }
    }
    else {
      //category or tag
      $slugname = $this->get_data("SELECT `slug` FROM `wp_terms` WHERE term_id = " . $this->conn->real_escape_string($object_id) . " LIMIT 1");
      if($slugname[0]['slug']) {
        //assume that translated slugname is: <slug>-<lang> (e.g. linux-en)
        $new_slugname = $slugname[0]['slug']."-".$this->target_lang;
        //get ID of new slug
        $id = $this->get_data("SELECT `term_id` FROM `wp_terms` WHERE slug = '" .$this->conn->real_escape_string($new_slugname). "' LIMIT 1");
        //return ID if entry found
        if($id) {
          return $id[0]['term_id'];
        }
      }
    }
  }

  function get_taxonomies($post_id, $taxonomy_filter = "category") {
    //get taxonomies (category, post_tag) per post
    $taxonomies = $this->get_data("SELECT tax.`term_id` FROM `wp_term_relationships` rel, `wp_term_taxonomy` tax WHERE rel.`term_taxonomy_id` = tax.`term_taxonomy_id` AND tax.`taxonomy` = '" . $this->conn->real_escape_string($taxonomy_filter) . "' AND rel.`object_id` = " . $this->conn->real_escape_string($post_id) .";");
    $new_taxs=[];
    if (sizeof($taxonomies) > 0) {
      //convert multi-dimensional array to single array
      foreach($taxonomies as $taxonomy) {
        array_push($new_taxs, $taxonomy['term_id']);
      }
    }
    return $new_taxs;
  }

  function set_taxonomy($post_id, $taxonomy_id) {
    //set taxonomies (category, post_tag) per post
    $count = $this->get_data("SELECT `count` FROM `wp_term_taxonomy` WHERE `term_taxonomy_id` = " . $this->conn->real_escape_string($taxonomy_id) . ";");
    //update database if counter read
    if($count) {
      $queries[0] = "INSERT INTO `wp_term_relationships` (object_id, term_taxonomy_id, term_order) VALUES (" . $this->conn->real_escape_string($post_id) . ", (SELECT `term_taxonomy_id` FROM wp_term_taxonomy WHERE `term_id`= " . $this->conn->real_escape_string($taxonomy_id) . "), 0);";
      $queries[1] = "UPDATE `wp_term_taxonomy` SET `count` = " . (intval($count)+1) . " WHERE `term_id` = " . $this->conn->real_escape_string($taxonomy_id) . ";";
      foreach($queries as $query) {
        //echo $query;
        $this->set_data($query);
      }
    }
  }

  function get_taxonomy_name($cat_id) {
    //retrieves a category's name
    $cat_name = $this->get_data("SELECT `name` FROM `wp_terms` WHERE `term_id` = " . $this->conn->real_escape_string($cat_id));
    //return name if entry found
    if ($cat_name[0]['name']) {
      return $cat_name[0]['name'];
    }
  }

  function get_post_title($post_id) {
    //retrieve a particular blog post's title
    $post_title = $this->get_data("SELECT `post_title` FROM `wp_posts` WHERE ID = " . $this->conn->real_escape_string($post_id));
    //return title if entry found
    if ($post_title[0]['post_title']) {
      return $post_title[0]['post_title'];
    }
  }

  function fix() {
    //show mode
    if ($this->simulation == true) {
      echo "No changes will be made as this is a <b>simulation</b><br />";
    }
    else {
      echo "Changes will be made, welcome to the <b>danger zone</b><br />";
    }

    //scan _all_ the posts
    $sql = "SELECT `ID`, `post_title` FROM `wp_posts` WHERE `post_status` = 'publish' AND `post_type` = 'post' AND `ID` IN (SELECT `element_id` FROM `wp_icl_translations` WHERE `language_code` = '" . $this->conn->real_escape_string($this->source_lang) . "' AND `source_language_code` IS NULL AND `element_type` = 'post_post') ORDER BY ID ASC";
    $result = $this->conn->query($sql);

    //show table
    if ($result->num_rows > 0) {
      echo "Found <b>" . $result->num_rows . "</b> posts (" . $this->source_lang . " language)<br /><br />";
      ?>
      <table style="width:100%;">
        <thead>
          <tr>
            <td>#</td>
            <td>Post ID</td>
            <td style="max-width:10%;">Post title</td>
            <td>Post categories</td>
            <td>Post tags</td>
            <td>Translation ID</td>
            <td style="max-width:10%;">Translation title</td>
            <td>Translation categories</td>
            <td>Translation tags</td>
            <td>Action</td>
          </tr>
        </thead>
        <tbody>
      <?php
      //iterate through posts
      $counter = 1;
      while($row = $result->fetch_assoc()){
        //print post information
        echo "<tr>";
        echo "<td>$counter</td>";

        //post ID
        echo "<td>" . $row['ID'] . "</td>";

        //post title
        echo "<td>" . $row['post_title'] . "</td>";

        //post categories
        $this_cats = $this->get_taxonomies($row['ID'], 'category');
        if($this_cats) {
          echo "<td><ul>";
          foreach($this_cats as $this_cat) {
            echo "<li>#" . $this_cat . " (" . $this->get_taxonomy_name($this_cat) . ")</li>";
          }
          echo "</ul></td>";
        }
        else {
          //no category found, ehh?
          echo "<td>None?</td>";
        }

        //post tags
        $this_tags = $this->get_taxonomies($row['ID'], "post_tag");
        if($this_tags) {
          echo "<td><ul>";
          foreach($this_tags as $this_tag) {
            //I gonna pop some tags
            echo "<li>#" . $this_tag . " (" . $this->get_taxonomy_name($this_tag) . ")</li>";
          }
          echo "</td></ul>";
        }
        else {
          //No tags
          echo "<td>None</td>";
        }

        //find translation
        $this_trans = $this->get_translation($row['ID']);
        if($this_trans) {
          //translation ID
          echo "<td>" . $this_trans . "</td>";

          //translation title
          echo "<td>" . $this->get_post_title($this_trans) . "</td>";

          //translation categories
          echo "<td><ul>";
          $this_target_cats = [];
          foreach($this_cats as $this_source_cat) {
            $this_target_cat = $this->get_translation($this_source_cat, "tax_category");
            if ($this_target_cat) {
              array_push($this_target_cats, $this_target_cat);
              echo "<li> #" . $this_target_cat . " (" . $this->get_taxonomy_name($this_target_cat) . ")";
            }
            else {
              echo "<li> #" . $this_source_cat . " (" . $this->get_taxonomy_name($this_source_cat) . ") ==> <b>not found</b>";
            }
          }
          echo "</ul></td>";

          //translation tags
          if($this_tags) {
            echo "<td><ul>";
            $this_target_tags = [];
            foreach($this_tags as $this_source_tag) {
              //I gonna pop some tags
              $this_target_tag = $this->get_translation($this_source_tag, "tax_post_tag");
              if ($this_target_tag) {
                array_push($this_target_tags, $this_target_tag);
                echo "<li> #" . $this_target_tag . " (" . $this->get_taxonomy_name($this_target_tag) . ")";
              }
              else {
                echo "<li> #" . $this_source_tag . " (" . $this->get_taxonomy_name($this_source_tag) . ") ==> <b>not found</b>";
              }
            }
            echo "</ul></td>";
          }
          else {
            //No tags
            echo "<td>None</td>";
          }

        }
        else {
          //No translation at all
          echo "<td><i>Not found</i></td>";
          echo "<td><i>Not found</i></td>";
          echo "<td><i>Not found</i></td>";
          echo "<td><i>Not found</i></td>";
          echo "<td><i>Not available</i></td>";
        }

        //actions
        if($this_trans) {
          echo "<td><ul>";
          $trans_cats = $this->get_taxonomies($this_trans, 'category');
          $trans_tags = $this->get_taxonomies($this_trans, 'post_tag');

          //fix categories
          foreach($this_target_cats as $cat) {
            if(is_array($trans_cats) && !in_array($cat, $trans_cats)) {
              //add to category
              if ($this->simulation == true) {
                echo "<li><b>Add</b> to category #".$cat."</li>";
              }
              else {
                $this->set_taxonomy($this_trans, $cat);
              }
            }
            else {
              echo "<li><i>Already in category #".$cat."</i></li>";
            }
          }

          //fix tags
          if($this_tags) {
            foreach($this_target_tags as $tag) {
              if(is_array($trans_tags) && !in_array($tag ,$trans_tags)) {
                //add tag
                if ($this->simulation == true) {
                  echo "<li><b>Add</b> tag #".$tag."</li>";
                }
                else {
                  $this->set_taxonomy($this_trans, $tag);
                }
              }
              else {
                echo "<li><i>Already has tag #".$tag."</i></li>";
              }
            }
          }

          echo "</ul></td>";
        }
        echo "</tr>";
        $counter++;
      }
      ?>
      </tbody>
    </table>
    <?php
    } else {
        echo "Error: No posts found, check language?<br />";
    }
    $this->conn->close();
  }

}

//convert simulation flag
if(isset($_POST['simulation']) && $_POST['simulation'] == "true") {
  $simulation = true;
}
else {
  $simulation = false;
}
//create new instance
$foo = new cleanup($_POST['database_server'], $_POST['database_name'], $_POST['username'], $_POST['password'], $_POST['source_lang'], $_POST['target_lang'], $simulation);
$foo->fix();

?>
</body>
</html>
