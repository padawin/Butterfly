<!DOCTYPE html>
<html>
    <head>
        <title>My new Butterfly Project</title>
        <meta name="viewport" content="width=device-width">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <?php
        foreach($this->getCss() as $styleSheet){?>
            <link href="/styles/<?=$styleSheet?>" rel="stylesheet" type="text/css" />
        <?php }

        foreach($this->getSecondaryCss() as $styleSheet){?>
            <link href="/<?=$styleSheet?>" rel="stylesheet" type="text/css" />
        <?php }

        foreach($this->getJs() as $jsSheet){?>
            <script type="text/javascript" src="/js/<?=$jsSheet?>"></script>
        <?php }

        foreach($this->getSecondaryJs() as $jsSheet){?>
            <script type="text/javascript" src="/js/<?=$jsSheet?>"></script>
        <?php }?>

    </head>

    <body>
        <header>
            <?php foreach($this->getAllWidgetsFromArea('header') as $tc_entete){$tc_entete->render();}?>
        </header>
        <div>
            <?php $this->getMainContent()->render();?>
        </div>
    </body>
</html>
