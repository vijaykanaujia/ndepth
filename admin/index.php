<?php // Silence is golden

// parse the data into a tree
function build_tree($data)
{
    $start = array();                                         // list of all root nodes
    $last = array();                                          // list of last-seen nodes on every depth

    foreach ($data as $row) {                                 // iterate over all rows
        $depth = 0;                                             // reset depth (start form left)
        foreach ($row as $cell) {                               // iterate over all cells in the row
            if (!is_null($cell) && $cell != '') {                   // empty cell? if so, ignore
                if ($depth == 0) {                                    // top layer?
                    $obj = new node($cell);                    // this is a root node
                    $start[] = $obj;                                  // add to list of root nodes
                    $last[0] = $obj;                                  // set as root node
                } else {
                    $parent = null;                                   // we want to find a parent object
                    for ($dd = $depth - 1; $dd >= 0; $dd--) {             // traverse up to root
                        if (isset($last[$dd])) {                        // $last for this level set?
                            $parent = $last[$dd];                         // accept it as parent
                            break;                                        // do not search further
                        }
                    }

                    if (!is_null($parent)) {                          // parent found?
                        $obj = new node($cell);         // create new node for this entry
                        $parent->addChild($obj);                        // attach it to the parent
                        $last[$depth] = $obj;                           // set object as $last for this level
                    }
                }
            }
            $depth++;                                             // increase depth (advance right)
        }
    }

    return $start;                                            // return list of root nodes
}
// show the tree descending from a single node given
function show_tree($node, $depth = 0)
{                     // recursively output nodes
    echo str_repeat('> ', $depth) . $node->getText() . PHP_EOL;  // this node
    foreach ($node->getChildren() as $subnode) {             // iterate over child nodes
        show_tree($subnode, $depth + 1);                          // recursive call for children
    }
}

function insert_data($node, $sheet_name, $parent_id = 0, $parent_value = '')
{
    $value = clear_string($node->getText());
    $parent_value = $parent_value ? $parent_value . '-' . $value : $value;
    if (count($node->getChildren())) {
        $parent_id = insert_row($node->getText(), $parent_value, $parent_id, $sheet_name);
        foreach ($node->getChildren() as $subnode) {
            $value = clear_string($subnode->getText());
            $sub_parent_value = $parent_value ? $parent_value . '-' . $value : $value;
            $sub_parent_id = insert_row($subnode->getText(), $sub_parent_value, $parent_id, $sheet_name);
            if (count($subnode->getChildren())) {
                for ($dd = 0; $dd < count($subnode->getChildren()); $dd++) {
                    insert_data($subnode->getChildren()[$dd], $sheet_name, $sub_parent_id, $sub_parent_value);
                }
            }
        }
    } else {
        insert_row($node->getText(), $parent_value, $parent_id, $sheet_name);
    }
}

function insert_row($name, $value, $parent_id, $sheet)
{
    global $wpdb;
    $table_name = "{$wpdb->prefix}ndepth";
    $wpdb->insert($table_name, [
        'category_list_tag' => 'demo-types',
        'parent_id' => $parent_id,
        'name' => $name,
        'value' => $value,
        'sub_tag' => $sheet
    ]);

    return $wpdb->insert_id;
}

function value_exists_in_table($value, $sheet)
{
    $value = addslashes($value);
    global $wpdb;
    $table_name = "{$wpdb->prefix}ndepth";
    $query = "SELECT * FROM `{$table_name}` WHERE `parent_id` = 0 AND `sub_tag` = '{$sheet}' AND `name` LIKE '{$value}'";
    return $wpdb->get_row($query);
}

function clear_string($string)
{
    $string = str_replace(" ", '_', $string); // Replaces all spaces with hyphens.
    $string = preg_replace('/[^A-Za-z0-9\-]/', '_', $string); // Removes special chars.
    return preg_replace('/-+/', '_', $string); // Replaces multiple hyphens with single one.
}

function make_tag_string($string)
{
    $string = str_replace(" ", '-', $string); // Replaces all spaces with hyphens.
    return strtolower(preg_replace('/-+/', '-', $string)); // Replaces multiple hyphens with single one.
}
