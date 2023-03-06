{OVERALL_GAME_HEADER}

<!-- 
--------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- 24/7: The Game BGA implementation: © Jim Ginn <ginn5j@gmail.com>
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
                <div id="space_{X}_{Y}" class="tf7_space" style="left: {LEFT}px; top: {TOP}px;"></div>
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

<div class='tf7_game_summary player-board' id="tf7_game_summary">
<span class="tf7_deck_size"><i class="tf7_deck_size_icon fa fa-shopping-bag" aria-hidden="true">&nbsp;</i><span class="tf7_deck_size_value"></span></span>
</div>

<script type="text/javascript">

// Javascript HTML templates
var jstpl_tile='<div class="tf7_tile tf7_tile_${value}" id="tile_${x_y}"></div>';
var jstpl_stone='<div class="tf7_stone" id="stone_${x_y}"></div>';
var jstpl_player_board='<div class="tf7_player_board" id="tf7_player_board_${player_id}">' +
    '<span class="tf7_hand_size">' +
        '<i class="tf7_hand_size_icon fa fa-hand-paper-o" aria-hidden="true"></i>&nbsp;' +
        '<span class="tf7_hand_size_value">${hand_size}</span>' +
    '</span>' +
'</div>';

</script>  

{OVERALL_GAME_FOOTER}
