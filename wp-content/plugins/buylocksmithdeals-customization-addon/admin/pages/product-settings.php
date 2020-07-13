<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
?>
<?php
    if(isset($_POST['submit']) && !empty($_POST['submit'])){
       if(isset($_FILES['edge_cut_image']["name"]) && !empty($_FILES['edge_cut_image']["name"])){
            $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/";
            $fileName = 'edge_cut_image'. '_' . basename($_FILES['edge_cut_image']['name']);
            $target_file = $target_dir . $fileName;

            if (move_uploaded_file($_FILES['edge_cut_image']["tmp_name"], $target_file)) {
                $file_edge_cut=$fileName;
            }
        }
        else{
            $file_edge_cut=$_POST['hidden_file_edge_cut'];
        }
        
        if(isset($_FILES['high_security_image']['name']) && !empty($_FILES['high_security_image']['name'])){
            $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/";
            $fileName = 'high_security_image'. '_' . basename($_FILES['high_security_image']['name']);
            $target_file = $target_dir . $fileName;

            if (move_uploaded_file($_FILES['high_security_image']["tmp_name"], $target_file)) {
                $file_high_security=$fileName;
            }
        }
        else{
           $file_high_security=$_POST['hidden_file_high_security'];
        }
        if(isset($_FILES['tibbe_image']['name']) && !empty($_FILES['tibbe_image']['name'])){
            $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/";
            $fileName = 'tibbe_image'. '_' . basename($_FILES['tibbe_image']['name']);
            $target_file = $target_dir . $fileName;

            if (move_uploaded_file($_FILES['tibbe_image']["tmp_name"], $target_file)) {
                $file_tibbe=$fileName;
            }
        }
        else{
           $file_tibbe=$_POST['hidden_file_tibbe'];
        }
        if(isset($_FILES['vats_image']['name']) && !empty($_FILES['vats_image']['name'])){
            $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/";
            $fileName = 'vats_image'. '_' . basename($_FILES['vats_image']['name']);
            $target_file = $target_dir . $fileName;

            if (move_uploaded_file($_FILES['vats_image']["tmp_name"], $target_file)) {
                $file_vats=$fileName;
            }
        }
        else{
           $file_vats=$_POST['hidden_file_vats'];
        }
        update_option('file_edge_cut',$file_edge_cut);
        update_option('file_high_security',$file_high_security);
        update_option('file_tibbe',$file_tibbe);
        update_option('file_vats',$file_vats);
    }
    
    if(isset($_POST['submit_locks']) && !empty($_POST['submit_locks'])){
        if(isset($_FILES['locks_image']["name"]) && !empty($_FILES['locks_image']["name"])){
            $target_dir = BUYLOCKSMITH_DEALS_PLUGIN_DIR . "/uploads/";
            $fileName = 'locks_image'. '_' . basename($_FILES['locks_image']['name']);
            $target_file = $target_dir . $fileName;

            if (move_uploaded_file($_FILES['locks_image']["tmp_name"], $target_file)) {
                $file_locks=$fileName;
            }
        }
        else{
            $file_locks=$_POST['hidden_file_locks'];
        }
        update_option('file_locks',$file_locks);
    }
	
	if(isset($_POST['submit_twillo']) && !empty($_POST['submit_twillo'])){
       update_option('twillo_sid',$_POST['twillo_sid']);
       update_option('twillo_token',$_POST['twillo_token']);
       update_option('twillo_phone_number',$_POST['twillo_phone_number']);
    }
        
        $file_edge_cut=get_option('file_edge_cut');
        $file_high_security=get_option('file_high_security');
        $file_tibbe= get_option('file_tibbe');
        $file_vats= get_option('file_vats');
        $file_locks= get_option('file_locks');
		$twillo_sid=get_option('twillo_sid');
		$twillo_token= get_option('twillo_token');
		$twillo_phone_number= get_option('twillo_phone_number');
        $target_dir_img = WP_PLUGIN_URL  .'/'.BUYLOCKSMITH_DEALS_BASE_FOLDER_NAME . "/uploads/";
?>

<h3>Car Key Images</h3>

<div class="container">
    <div class="row">
        <form name="car-key-type" id="car-key-type" method="post" enctype= multipart/form-data>
            <div class="form-control">
                <label for="edge_cut_image">Double-Sided Image</label>
                <input type="file" name="edge_cut_image" id="edge_cut_image" >
                <?php if(!empty($file_edge_cut)){ ?>
                <img src="<?php echo $target_dir_img.$file_edge_cut; ?>" height="50" width="50">
                <?php } ?>
                <input type="hidden" name="hidden_file_edge_cut" id="hidden_file_edge_cut" value="<?php echo $file_edge_cut; ?>" >
            </div>
            <div class="form-control">
                <label for="high_security_image">High-Security Image</label>
                <input type="file" name="high_security_image" id="high_security_image" >
                <?php if(!empty($file_high_security)){ ?>
                <img src="<?php echo $target_dir_img.$file_high_security; ?>" height="50" width="50">
                <?php } ?>
                <input type="hidden" name="hidden_file_high_security" id="hidden_file_high_security" value="<?php echo  $file_high_security; ?>" >
            </div>
            <div class="form-control">
                <label for="tibbe_image">Tibbe Image</label>
                <input type="file" name="tibbe_image" id="tibbe_image" >
                 <?php if(!empty($file_tibbe)){ ?>
                <img src="<?php echo $target_dir_img.$file_tibbe; ?>" height="50" width="50">
                <?php } ?>
                <input type="hidden" name="hidden_file_tibbe" id="hidden_file_tibbe" value="<?php echo $file_tibbe; ?>" >
            </div>
            <div class="form-control">
                <label for="vats_image">VATS key Image</label>
                <input type="file" name="vats_image" id="vats_image" >
                 <?php if(!empty($file_vats)){ ?>
                <img src="<?php echo $target_dir_img.$file_vats; ?>" height="50" width="50">
                <?php } ?>
                <input type="hidden" name="hidden_file_vats" id="hidden_file_vats" value="<?php echo $file_vats; ?>" >
            </div>
             <div class="form-control button-submit">
                 <input type="submit" name="submit" id="submit" value="submit" class="button button-primary button-large">
             </div>
               
        </form>
    </div>
</div>
<h3>Locks Images</h3>
<div class="container">
    <div class="row">
        <form name="locks_example" id="locks_example" method="post" enctype= multipart/form-data>
            <div class="form-control">
                <label for="locks_image">Locks Image</label>
                <input type="file" name="locks_image" id="locks_image" >
                <?php if(!empty($file_locks)){ ?>
                <img src="<?php echo $target_dir_img.$file_locks; ?>" height="50" width="50">
                <?php } ?>
                <input type="hidden" name="hidden_file_locks" id="hidden_file_locks" value="<?php echo $file_locks; ?>" >
            </div>
             <div class="form-control button-submit">
                 <input type="submit" name="submit_locks" id="submit_locks" value="submit" class="button button-primary button-large">
             </div>
        </form>
    </div>
 </div>   
 <h3>Twillo Credential</h3>
<div class="container">
    <div class="row">
        <form name="locks_example" id="locks_example" method="post" enctype= multipart/form-data>
            <div class="form-control">
                <label for="twillo_sid">Twillo SID</label>
                <input type="text" name="twillo_sid" id="twillo_sid" value="<?php echo $twillo_sid; ?>" >
			</div>
			<div class="form-control">
                <label for="twillo_token">Twillo Token</label>
                <input type="text" name="twillo_token" id="twillo_token" value="<?php echo $twillo_token; ?>" >
			</div>
			<div class="form-control">
                <label for="twillo_token">Twillo Phone number</label>
                <input type="text" name="twillo_phone_number" id="twillo_phone_number" value="<?php echo $twillo_phone_number; ?>" >
			</div>
             <div class="form-control button-submit">
                 <input type="submit" name="submit_twillo" id="submit_twillo" value="submit" class="button button-primary button-large">
             </div>
        </form>
    </div>
 </div>  
<style>
    .button-submit {
    border-bottom: none !important;
}
    form#car-key-type {
        background: #fff;
        padding: 10px;
    }
    form#locks_example {
        background: #fff;
        padding: 10px;
        margin-top: 25px;
    }
    input#tibbe_image {
        margin-left: 45px;
    }
    input#vats_image {
        margin-left: 25px;
    }
    input#locks_image {
        margin-left: 125px;
    }
    #car-key-type label {
        margin-right: 84px;
    }
    input#edge_cut_image {
        margin-left: 25px;
    }
    
    form#car-key-type .form-control {
    padding: 10px 0;
    border-bottom: 1px solid #d3d3d3;
    } 
    form#locks_example .form-control {
    padding: 10px 0;
    border-bottom: 1px solid #d3d3d3;
    } 

form#car-key-type .form-control img {
    vertical-align: bottom;
}
input#edge_cut_image {
    margin-left: 0;
}

#car-key-type label {
    margin-right: 84px;
    vertical-align: top;
}

form#car-key-type .form-control input {
    vertical-align: top;
}
    </style>