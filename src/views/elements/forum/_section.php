<div class="panel panel-default">
    <div class="panel-heading" role="tab" id="headingOne">
        <h4 class="panel-title">
            <a data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne" class="pull-right">
                <span class="glyphicon glyphicon-chevron-up"></span>
            </a>
            <a href="">Forum jeden</a>
        </h4>
    </div>
    <div id="collapseOne" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
        <?= $this->render('/elements/forum/_forums') ?>
    </div>
</div>