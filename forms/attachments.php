<div class="wrap">
    <div class="icon32" id="icon-upload"><br></div>
    <h2><?php echo "GD bbPress Attachments"; ?></h2>

    <?php if (isset($_GET["settings-updated"]) && $_GET["settings-updated"] == "true") { ?>
    <div class="updated settings-error" id="setting-error-settings_updated"> 
        <p><strong><?php _e("Settings saved.", "gd-bbpress-attachments"); ?></strong></p>
    </div>
    <?php } ?>

    <div class="d4p-settings">
        <form action="" method="post">
        <?php wp_nonce_field("gd-bbpress-attachments"); ?>
        <h3><?php _e("Global Attachments Settings", "gd-bbpress-attachments"); ?></h3>
        <p><?php _e("These settings can be overriden for individual forums.", "gd-bbpress-attachments"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="max_file_size"><?php _e("Maximum file size", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="text" class="small-text" value="<?php echo $options["max_file_size"]; ?>" id="max_file_size" name="max_file_size" />
                        <span class="description">KB</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="max_to_upload"><?php _e("Maximum files to upload", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="text" class="small-text" value="<?php echo $options["max_to_upload"]; ?>" id="max_to_upload" name="max_to_upload" />
                        <span class="description"><?php _e("For single topic or reply", "gd-bbpress-attachments"); ?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("Users Upload Restrictions", "gd-bbpress-attachments"); ?></h3>
        <p><?php _e("Only users having one of the selected roles will be able to attach files.", "gd-bbpress-attachments"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><?php _e("Allow upload to", "gd-bbpress-attachments") ?></th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span><?php _e("Allow upload to", "gd-bbpress-attachments"); ?></span></legend>
                            <?php foreach ($wp_roles->role_names as $role => $title) { ?>
                            <label for="roles_to_upload_<?php echo $role; ?>">
                                <input type="checkbox" <?php if (!isset($options["roles_to_upload"]) || is_null($options["roles_to_upload"]) || in_array($role, $options["roles_to_upload"])) echo " checked"; ?> value="<?php echo $role; ?>" id="roles_to_upload_<?php echo $role; ?>" name="roles_to_upload[]" />
                                <?php echo $title; ?>
                            </label><br/>
                            <?php } ?>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("Forums Integration", "gd-bbpress-attachments"); ?></h3>
        <p><?php _e("With these options you can modify the forums to include attachment elements.", "gd-bbpress-attachments"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="attachment_icon"><?php _e("Attachment Icon", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["attachment_icon"] == 1) echo " checked"; ?> name="attachment_icon" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="attachment_icon"><?php _e("File Type Icons", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["attchment_icons"] == 1) echo " checked"; ?> name="attchment_icons" />
                    </td>
                </tr>
            </tbody>
        </table>
        <h3><?php _e("JavaScript and CSS Settings", "gd-bbpress-attachments"); ?></h3>
        <p><?php _e("You can disable including styles and JavaScript by the plugin, if you want to do it some other way.", "gd-bbpress-attachments"); ?></p>
        <table class="form-table">
            <tbody>
                <tr valign="top">
                    <th scope="row"><label for="max_file_size"><?php _e("Include JavaScript", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["include_js"] == 1) echo " checked"; ?> name="include_js" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="max_file_size"><?php _e("Include CSS", "gd-bbpress-attachments"); ?></label></th>
                    <td>
                        <input type="checkbox" <?php if ($options["include_css"] == 1) echo " checked"; ?> name="include_css" />
                    </td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" value="<?php _e("Save Changes", "gd-bbpress-attachments"); ?>" class="button-primary" id="gdbb-attach-submit" name="gdbb-attach-submit" /></p>
    </form>
    </div>

    <div class="d4p-information">
        <div class="d4p-plugin">
            <h3>GD bbPress Attachments <?php echo $options["version"]; ?></h3>
            <?php
            
            $status = ucfirst($options["status"]);
            if ($options["revision"] > 0) {
                $status.= " #".$options["revision"];
            }

            _e("Release Date: ", "gd-bbpress-attachments");
            echo '<strong>'.$options["date"]."</strong> | ";
            _e("Status: ", "gd-bbpress-attachments");
            echo '<strong>'.$status."</strong> | ";
            _e("Build: ", "gd-bbpress-attachments");
            echo '<strong>'.$options["build"]."</strong>";

            ?>
        </div>
        <h3><?php _e("Important Plugin Links", "gd-bbpress-attachments"); ?></h3>
        <a target="_blank" href="http://www.dev4press.com/plugins/gd-bbpress-attachments/">GD bbPress Attachments <?php _e("Home Page", "gd-bbpress-attachments"); ?></a><br/>
        <a target="_blank" href="http://wordpress.org/extend/plugins/gd-bbpress-attachments/">GD bbPress Attachments <?php _e("on", "gd-bbpress-attachments"); ?> WordPress.org</a>
        <h3><?php _e("Dev4Press Important Links", "gd-bbpress-attachments"); ?></h3>
        <a target="_blank" href="http://twitter.com/milang">Dev4Press <?php _e("on", "gd-bbpress-attachments"); ?> Twitter</a><br/>
        <a target="_blank" href="http://www.facebook.com/dev4press">Dev4Press Facebook <?php _e("Fan Page", "gd-bbpress-attachments"); ?></a>
        <h3><?php _e("Dev4Press Premium Plugins And Themes", "gd-bbpress-attachments"); ?></h3>
        <a target="_blank" href="http://www.dev4press.com/gd-press-tools/">GD Press Tools</a><br/>
        <a target="_blank" href="http://www.dev4press.com/gd-products-center/">GD Products Center</a><br/>
        <a target="_blank" href="http://www.dev4press.com/gd-taxonomies-tools/">GD Custom Posts And Taxonomies Tools</a><br/>
        <a target="_blank" href="http://www.gdaffiliatecenter.com/">GD Affiliate Center</a><br/>
        <a target="_blank" href="http://www.dev4press.com/plugins/gd-azon-fusion/">GD aZon FUSION</a><br/>
        <a target="_blank" href="http://www.gdstarrating.com/">GD Star Rating</a><br/>
        <a target="_blank" href="http://www.dev4press.com/themes/">xScape Themes</a>
        <div class="d4p-copyright">
            Dev4Press &copy; 2008 - 2011 <a target="_blank" href="http://www.dev4press.com">www.dev4press.com</a>
            <br/>Golden Dragon WebStudio <a target="_blank" href="http://www.gdragon.info">www.gdragon.info</a>
        </div>
    </div>
</div>