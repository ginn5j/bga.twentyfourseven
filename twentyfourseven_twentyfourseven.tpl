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
var jstpl_help_dialog='<div class="tf7_help_html" id="tf7_help_html">' +
    '<div>' +
        '<span class="tf7_deck_size">' +
            '<i class="tf7_deck_size_icon fa fa-shopping-bag" aria-hidden="true"></i>&nbsp;' +
            '<span class="tf7_deck_size_value">24</span>' +
        '</span>' +
        ' : Tiles available to draw.' +
    '</div>' +
    '<div>' +
        '<span class="tf7_hand_size">' +
            '<i class="tf7_hand_size_icon fa fa-hand-paper-o" aria-hidden="true"></i>&nbsp;' +
            '<span class="tf7_hand_size_value">6</span>' +
        '</span>' +
        " : Tiles in a player's hand." +
    '</div>' +
    '<div>' +
        '<table class="tf7_combo_summary" id="tf7_combo_summary">' +
            '<thead>' +
                '<tr><th>Combination</th><th>Minutes</th></tr>' +
            '</thead>' +
            '<tbody>' +
                '<tr><td>Sum of 7 (all tiles, 2+ tiles)</td><td>20</td></tr>' +
                '<tr><td>Run of 3</td><td>30</td></tr>' +
                '<tr><td>Sum of 24 (all tiles)</td><td>40</td></tr>' +
                '<tr><td>Run of 4</td><td>40</td></tr>' +
                '<tr><td>Set of 3 (3 of a Kind)</td><td>50</td></tr>' +
                '<tr><td>Run of 5</td><td>50</td></tr>' +
                '<tr><td>Set of 4 (4 of a Kind)</td><td>60</td></tr>' +
                '<tr><td>Run of 6</td><td>60</td></tr>' +
                '<tr><td>Bonus<br>- Sum of 7 and Sum of 24<br>- Sum of 24 in 7 Tiles</td><td>60</td></tr>' +
            '</tbody>' +
        '</table>' +
    '</div>' +
'</div>';

</script>  

{OVERALL_GAME_FOOTER}
