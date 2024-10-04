<?php
?>

<!DOCTYPE html>
<html lang="ja" <?php if (isset($html)) : echo $html;
                endif ?>>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
    <?php if (isset($css)) : echo $css;
    endif; ?>

    <?php if (isset($link)) : echo $link;
    endif; ?>
    <title>
        <?php if (isset($title)) : echo $title . ' - ';
        endif; ?>販売管理
    </title>
</head>

<body <?php if (isset($body)) : echo $body;
        endif; ?>>

    <?php echo $content; ?>

</body>

</html>
