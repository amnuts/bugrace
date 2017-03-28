<?php

require 'vendor/autoload.php';

use Treffynnon\Navigator as N;
use Treffynnon\Navigator\Distance\Converter\MetreToKilometre as KM;
use Treffynnon\Navigator\Distance\Converter\MetreToMile as M;
use Treffynnon\Navigator\Coordinate as C;
use Treffynnon\Navigator\Coordinate\DmsParser as DMS;

$people = array_merge(
    ['rosie' => [], 'luke' => [], 'lisa' => [], 'andy' => []],
    @include __DIR__ . '/people.php'
);

$waypoints = [];
if (file_exists(__DIR__.'/geocaching.loc')) {
    $waypointXml = simplexml_load_file(__DIR__.'/geocaching.loc');
    foreach ($waypointXml->waypoint as $waypoint) {
        $waypoints[(string)$waypoint->name['id']] = [
            'id'   => (string)$waypoint->name['id'],
            'name' => preg_replace('/^(.*?) by .*\r?$/', '$1', trim((string)$waypoint->name)),
            'lat'  => (string)$waypoint->coord['lat'],
            'lon'  => (string)$waypoint->coord['lon']
        ];
    }
}

$maxTravelled = $maxVisited = 0;
foreach ($people as $person => $cacheData) {
    $data = [
        'bug'       => @$cacheData['bug'],
        'caches'    => [],
        'visited'   => 0,
        'travelled' => 0
    ];
    for ($i = 0; $i < count($cacheData['caches']); $i++) {
        if (isset($waypoints[$cacheData['caches'][$i]])) {
            $data['caches'][$i] = $waypoints[$cacheData['caches'][$i]];
            $lat = new C($waypoints[$cacheData['caches'][$i]]['lat']);
            $lon = new C($waypoints[$cacheData['caches'][$i]]['lon']);
            $lat->setParser(new DMS());
            $lon->setParser(new DMS());
            list($latH, $latM, $latS) = explode(' ', (string)$lat);
            list($lonH, $lonM, $lonS) = explode(' ', (string)$lon);
            $data['caches'][$i]['ddm'] = sprintf('%s %d° %00.3F %s %d° %00.3F',
                ($latH < 0 ? 'S' : 'N'), $latH, ($latM + ($latS/60)),
                ($lonH < 0 ? 'W' : 'E'), $lonH, ($lonM + ($lonS/60))
            );
        } else {
            $data['caches'][$i] = ['id' => $cacheData['caches'][$i]];
        }
        $data['caches'][$i]['travelled'] = 0;
        if ($i && isset($data['caches'][$i]['lat']) && isset($data['caches'][$i-1]['lat'])) {
            $data['caches'][$i]['travelled'] = N::getDistance(
                $data['caches'][$i-1]['lat'], $data['caches'][$i-1]['lon'],
                $data['caches'][$i]['lat'], $data['caches'][$i]['lon']
            );
            $data['travelled'] += $data['caches'][$i]['travelled'];
        }
    }
    $data['visited'] = count($data['caches']);
    $maxVisited = max($maxVisited, $data['visited']);
    $maxTravelled = max($maxTravelled, $data['travelled']);
    $data['caches'] = array_reverse($data['caches']);
    $people[$person] = $data;
}

$sameTravelled = $sameVisited = 0;
foreach ($people as $person => $cacheData) {
    if ($maxVisited == $cacheData['visited']) {
        ++$sameVisited;
    }
    if ($maxTravelled == $cacheData['travelled']) {
        ++$sameTravelled;
    }
}

$toMiles = new M();
$toKilometres = new KM();

?>
<!doctype html>
<html class="no-js" lang="en" dir="ltr" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geocaching travel bug race</title>
    <link rel="stylesheet" href="css/foundation.min.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="css/app.css">
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:creator" content="@acollington" />
    <meta property="og:url" content="http://bugrace.amnuts.com/" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="Geocaching Travel Bug Race" />
    <meta property="og:description" content="Help our bugs travel the world" />
    <meta property="og:image" content="http://bugrace.amnuts.com/img/og-image.jpg" />
</head>
<body>

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
    ga('create', 'UA-5122767-4', 'auto');
    ga('send', 'pageview');
</script>

<header>
    <div class="image-header">
        <img src="img/hills.jpg"/>
    </div>
    <div class="row">
        <div class="small-12 columns">
            <h1>Geocaching Travel Bug Race</h1>
            <h2>Help our bugs travel the world</h2>
        </div>
    </div>
</header>

<main>
    <div class="row">
        <div class="row">
            <div class="small-12 medium-6 columns text-center">
                <div id="distance-toggler">distances in <a href="#miles" class="selected">miles</a> / <a href="#km">km</a></div>
            </div>
            <div class="small-12 medium-6 columns text-center">
                <div id="location-toggler">location as <a href="#ne" class="selected">north/easting</a> / <a href="#ll">longitude/latitude</a></div>
            </div>
        </div>

        <?php foreach ($people as $person => $data): ?>
        <?php $tM = $toMiles->convert($data['travelled']); ?>
        <?php $tKM = $toKilometres->convert($data['travelled']); ?>
        <div class="large-3 medium-6 small-12 columns">
            <div class="card">
                <aside><span><?php echo ucfirst($person); ?></span></aside>
                <img src="img/<?php echo $person; ?>.png" />
                <div>
                    <aside>
                        <span data-distance
                              data-miles="<?php echo !$tM ? '0' : sprintf('%.02f', $tM); ?>"
                              data-km="<?php echo !$tKM ? '0' : sprintf('%.02f', $tKM); ?>"
                        ><?php echo !$tM ? '0' : sprintf('%.02f', $tM); ?></span>
                        <span data-distance
                              data-miles="mile<?php echo $tM == 1 ? '' : 's'; ?>"
                              data-km="km"
                        >mile<?php echo $tM == 1 ? '' : 's'; ?></span>
                        <?php if ($data['travelled'] && $data['travelled'] == $maxTravelled): ?>
                        <span class="fa-stack winner-left <?= ($sameTravelled >= 3 ? 'bronze' : ($sameTravelled == 2 ? 'silver' : 'gold')); ?>">
                            <i class="fa fa-certificate fa-spin fa-fw fa-stack-2x"></i>
                            <i class="fa fa-trophy fa-fw fa-stack-1x"></i>
                        </span>
                        <?php endif; ?>
                    </aside>
                    <aside>
                        <span><?php echo $data['visited']; ?></span>
                        <span>cache<?php echo $data['visited'] == 1 ? '' : 's'; ?></span>
                        <?php if ($data['visited'] > 1 && $data['visited'] == $maxVisited): ?>
                        <span class="fa-stack winner-right <?= ($sameVisited >= 3 ? 'bronze' : ($sameVisited == 2 ? 'silver' : 'gold')); ?>">
                            <i class="fa fa-certificate fa-spin fa-fw fa-stack-2x"></i>
                            <i class="fa fa-trophy fa-fw fa-stack-1x"></i>
                        </span>
                        <?php endif; ?>
                    </aside>
                </div>
            </div>
            <?php if (!empty($data['caches'])): ?>
            <ol class="trail">
                <?php if (!empty($data['bug'])): ?>
                <span>
                    <a href="https://coord.info/<?= $data['bug']; ?>" target="_blank"><i class="fa fa-fw fa-bug"></i> <?= $data['bug']; ?></a>
                </span>
                <?php endif; ?>
                <?php foreach ($data['caches'] as $i => $cache): ?>
                    <?php $tM = $toMiles->convert($cache['travelled']); ?>
                    <?php $tKM = $toKilometres->convert($cache['travelled']); ?>
                    <li<?php if ($i): ?> class="hide-for-small-only"<?php endif; ?>>
                        <?php if (!$i): ?><p class="currently">currently at</p><?php endif; ?>
                        <a href="https://coord.info/<?php echo $cache['id']; ?>" target="_blank"><?php
                            echo !empty($cache['name']) ? htmlentities($cache['name'], ENT_COMPAT, 'utf-8') : $cache['id'];
                        ?></a>
                        <? if (isset($cache['lat'])): ?>
                        <p><b data-location data-ne="<?php echo $cache['ddm']; ?>" data-ll="<?php echo "{$cache['lat']}, {$cache['lon']}"; ?>"><?php echo $cache['ddm']; ?></b><br/><?php echo $cache['id']; ?></p>
                        <? endif; ?>
                    </li>
                    <? if ($cache['travelled']): ?>
                        <aside class="hide-for-small-only">
                        <span data-distance
                              data-miles="<?php echo $cache['travelled'] ? sprintf('%.02f', $tM).'<br/>m' : '??'; ?>"
                              data-km="<?php echo $cache['travelled'] ? sprintf('%.02f', $tKM).'<br/>km' : '??'; ?>"
                        ><?php echo $cache['travelled'] ? sprintf('%.02f', $tM).'<br/>m' : '??'; ?></span>
                        </aside>
                    <? endif; ?>
                <?php endforeach; ?>
            </ol>
            <? endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<footer>
    <div class="row">
        <div class="small-12 medium-9 columns">
            <p>Thanks for helping us make an adventure out of our travel bugs!</p>
            <p>Please geocache safely and remember to always be respectful of the land.</p>
        </div>
        <div class="small-12 medium-3 columns">
            <img src="img/cito.png" title="The 'Cache In Trash Out' Logo is a trademark of Groundspeak, Inc. Used with permission." />
        </div>
    </div>
</footer>

<script src="js/vendor/jquery.js"></script>
<script src="js/vendor/what-input.js"></script>
<script src="js/vendor/foundation.min.js"></script>
<script src="js/app.js"></script>
</body>
</html>
