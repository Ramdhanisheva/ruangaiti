<?php
// get_cat_sizes.php
$files = glob('uploads/category/*');
foreach ($files as $file) {
    if (is_file($file)) {
        $size = getimagesize($file);
        if ($size) {
            echo "$file: {$size[0]}x{$size[1]}\n";
        }
    }
}
