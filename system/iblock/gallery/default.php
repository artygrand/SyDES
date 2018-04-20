<div class="gallery">
    <?php foreach ($result as $img){ ?>
        <a href="<?=$img;?>" rel="lightbox[<?=$args['folder'];?>]"><img src="/cache/img/<?=$args['width'];?>_<?=$args['height'];?>_c/<?=$img;?>" alt=""></a>
    <?php } ?>
</div>

<?=H::pagination($page['fullpath'], $count, $skip, $args['perpage']);?>
