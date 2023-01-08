{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- TwentyFourSeven implementation : © Jim Ginn ginn5j@gmail.com
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-------

    twentyfourseven_twentyfourseven.tpl
    
-->
<div id="tf7_table">

    <div id="tf7_board">

        <div id="board">
            <!-- BEGIN space -->
                <div id="space_{X}_{Y}" class="space" style="left: {LEFT}px; top: {TOP}px;"></div>
            <!-- END space -->
            <div id="pieces">
            </div>
        </div>

    </div>

    <div id="tf7_player">

        <div id="tf7_player_hand">
        </div>

    </div>

</div>

<script type="text/javascript">

// Javascript HTML templates
var jstpl_tile='<div class="tile tile_${value}" id="tile_${x_y}"></div>';
var jstpl_stone='<div class="stone" id="stone_${x_y}"></div>';

</script>  

{OVERALL_GAME_FOOTER}
