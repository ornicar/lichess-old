<?php if(!$pager->pageCount) return ?>
<div class="pagination">
    <?php if(isset($pager->previous)): ?>
    <a class="previous" href="<?php echo $url.'?page='.$pager->previous ?>">Prev</a>
    <?php else: ?>
    <span class="previous">Prev</span>
    <?php endif ?>
    <!-- Numbered page links -->
    <?php foreach ($pager->pagesInRange as $page): ?>
    <?php if ($page != $pager->current): ?>
        <a href="<?php echo $url.'?page='.$page; ?>"><?php echo $page; ?></a> |
    <?php else: ?>
        <?php echo $page; ?> |
    <?php endif; ?>
    <?php endforeach; ?>
    <?php if(isset($pager->next)): ?>
    <a class="next" href="<?php echo $url.'?page='.$pager->next ?>">Next</a>
    <?php else: ?>
    <span class="next">Next</span>
    <?php endif ?>
</div>
