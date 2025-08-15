<?php if (!empty($articleArchives)) : ?>
<div class="p-4">
    <h4 class="fst-italic"><?= __('Archives') ?></h4>
    <ol class="list-unstyled mb-0">
        <?php foreach ($articleArchives as $year => $months) : ?>
            <li>
                <?php
                $isYearActive = ($this->request->getQuery('year') == $year);

                echo $this->Html->link(
                    $year,
                    [
                        'lang' => $this->request->getParam('lang'),
                        '_name' => 'home',
                        '?' => ['year' => $year],
                    ],
                    [
                        'class' => $isYearActive
                            ? 'fw-bold text-decoration-none'
                            : 'text-decoration-none',
                    ],
                );
                ?>
                <ol class="list-unstyled ms-3">
                    <?php foreach ($months as $month) : ?>
                    <li>
                        <?php
                        $isActive = (
                            $this->request->getQuery('year') == $year &&
                            $this->request->getQuery('month') == $month
                        );
                        echo $this->Html->link(
                            DateTime::createFromFormat('!m', $month)->format('F'),
                            [
                                'lang' => $this->request->getParam('lang'),
                                '_name' => 'home',
                                '?' =>
                                    [
                                        'year' => $year,
                                        'month' => $month,
                                    ],
                            ],
                            [
                                'class' => $isActive
                                    ? 'fw-bold text-decoration-none'
                                    : 'text-decoration-none',
                            ],
                        );
                        ?>
                    </li>
                    <?php endforeach; ?>
                </ol>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
<?php endif; ?>