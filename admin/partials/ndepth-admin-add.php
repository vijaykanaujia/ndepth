<?php
ini_set('error_reporting', E_ALL);
ini_set('display_errors', true);

require_once WP_PLUGIN_DIR . '/ndepth/packages/simplexlsx-master/src/SimpleXLSX.php';
require_once WP_PLUGIN_DIR . '/ndepth/admin/classes/node-class.php';
require_once WP_PLUGIN_DIR . '/ndepth/admin/index.php';

global $wpdb;
$table = $wpdb->prefix.'ndepth';
$update = false;

if (isset($_POST['import_ndepth_sheet'])) {

    if (!file_exists(WP_PLUGIN_DIR . '/temp')) {
        mkdir(WP_PLUGIN_DIR . '/temp', 0777, true);
    }

    $target_dir = WP_PLUGIN_DIR . '/temp/';
    $target_file = $target_dir . basename($_FILES["xls_sheet"]["name"]);

    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (move_uploaded_file($_FILES["xls_sheet"]["tmp_name"], $target_file)) {
        $xlsx = SimpleXLSX::parse($target_file);
        $sheets = $xlsx->sheetNames();

        foreach ($sheets as $sheet_key => $sheet_value) {
            $templist = [];
            foreach ($xlsx->rows($sheet_key) as $k => $r) {
                if (!($k == 0)) {
                    $templist[$k] = $r;
                }
            }

            //print_r(build_tree($templist));
            $rootNodes = build_tree($templist);

            foreach ($rootNodes as $node) {
                // show_tree($node);
                if (value_exists_in_table($node->getText(), make_tag_string($sheet_value))) {
                    echo "'" . $node->getText() . "' already exits <br/>";
                    continue;
                } else {
                    insert_data($node, make_tag_string($sheet_value));
                }
            }
        }
        echo "inserted";
        if (file_exists($target_file)) {
            unlink($target_file);
        }
    } else {
        echo "Something went wrong";
        die;
    }
}



if (isset($_POST['update_data'])) {
    $data = [
        'name'       => isset($_POST['name']) ? $_POST['name'] : null,
        'value' => isset($_POST['value']) ? $_POST['value'] : null,
        'image_id' => isset($_POST['image_id']) ? $_POST['image_id'] : null,
    ];

    // print_r($data);
    // die;

    $res = $wpdb->update($table,$data, ['id' => $_POST['id']]);
    if($res){
        echo '<p style="color: green;">data updated successfully</p>';
    };
}

if(isset($_GET['action']) && $_GET['action'] == 'edit'){
    $result = $wpdb->get_results('SELECT * FROM `'.$table.'` WHERE `id`='.$_GET['edit_id'].'')[0];
    $update = true;
}

?>
<div class="wrap">
    <h1><?php echo !$update ? esc_html(get_admin_page_title()) : 'Edit Data'; ?></h1>
    <?php if($update){ ?>
        <form action="" enctype="multipart/form-data" method="post">
        <input type="hidden" name="id" value="<?php echo $result->id; ?>">
        <div class="">
            <table width="100%">
                <tr>
                    <td>Category Tag</td>
                    <td>:</td>
                    <td><input type="text" name="category_list_tag" value="<?php echo $update ? $result->category_list_tag : '' ?>" readonly></td>
                </tr>
                <tr>
                    <td>Name</td>
                    <td>:</td>
                    <td><input type="text" name="name" value="<?php echo $update ? $result->name : '' ?>" required></td>
                </tr>
                <tr>
                    <td>Value</td>
                    <td>:</td>
                    <td><input type="text" name="value" value="<?php echo $update ? $result->value : '' ?>" required></td>
                </tr>
                <tr>
                    <td>Parent</td>
                    <td>:</td>
                    <td>
                        <select class="ndepth-parent" name="parent">
                            <?php
                                if($update){
                                    echo '<option value="'.$result->parent.'" selected="selected">'.$result->parent.'</option>';
                                }
                            ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td>Sub Tag</td>
                    <td>:</td>
                    <td><input type="text" name="sub_tag" value="<?php echo $update ? $result->sub_tag : '' ?>" readonly></td>
                </tr>
                <tr>
                    <td>Image</td>
                    <td>:</td>
                    <td>
                        <div style="display: flex;">
                        <input type="hidden" id="ndepth_image" name="image_id" value="<?php echo $update ? $result->image_id : '' ?>">
                        <input type="button" class="button-primary" value="Select a image" id="ndepth_browse_media">
                        <?php 
                            if($update && isset($result) && $image_id = $result->image_id){
                                $image = wp_get_attachment_image( $image_id, 'medium', false, array( 'id' => 'ndepth-preview-image' ) );
                            }else{
                                $image = '<img id="ndepth-preview-image" src="'.plugin_dir_url(__DIR__).'images/faker.png" />';
                            }
                            echo $image;
                        ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td></td>
                    <td>
                        <input class="button-primary form-action-btn" name="update_data" type="submit" value="submit" />
                        <a class="button-primary form-action-btn back-btn" href="?page=ndepth/admin/partials/ndepth-admin-display.php">Back</a>
                    </td>
                </tr>
            </table>
        </div>
    </form>
    <?php }else{ ?>
        <form id="upload_form" action="" enctype="multipart/form-data" method="post">
            <div class="welcome-panel">
                <p><input name="xls_sheet" type="file" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" /></p>
                <p>
                    <input class="button-primary" name="import_ndepth_sheet" id="btnSubmit" type="submit" value="Import" />
                    <a class="button-primary" href="?page=ndepth/admin/partials/ndepth-admin-display.php">Back</a>
                </p>
            </div>
        </form>
    <?php } ?>
</div>