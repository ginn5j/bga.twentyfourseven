<?php
 /**
  *------
  * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
  * 24/7: The Game BGA implementation: © Jim Ginn <ginn5j@gmail.com>
  *
  * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
  * See http://en.boardgamearena.com/#!doc/Studio for more information.
  * -----
  *
  * twentyfourseven.game.php
  *
  * This is the main file for your game logic.
  *
  * In this PHP file, you are going to defines the rules of the game.
  *
  */


require_once( APP_GAMEMODULE_PATH.'module/table/table.game.php' );


class TwentyFourSeven extends Table
{

    private const SUM_OF_7  = "sum-of-7";
    private const SUM_OF_24 = "sum-of-24";
    private const RUN_OF_3  = "run-of-3";
    private const RUN_OF_4  = "run-of-4";
    private const RUN_OF_5  = "run-of-5";
    private const RUN_OF_6  = "run-of-6";
    private const SET_OF_3  = "set-of-3";
    private const SET_OF_4  = "set-of-4";
    private const BONUS     = "bonus";

    private const STAT_SUM_OF_7   = "tally_sum_of_7";
    private const STAT_SUM_OF_24  = "tally_sum_of_24";
    private const STAT_RUN_OF_3   = "tally_run_of_3";
    private const STAT_RUN_OF_4   = "tally_run_of_4";
    private const STAT_RUN_OF_5   = "tally_run_of_5";
    private const STAT_RUN_OF_6   = "tally_run_of_6";
    private const STAT_SET_OF_3   = "tally_set_of_3";
    private const STAT_SET_OF_4   = "tally_set_of_4";
    private const STAT_BONUS      = "tally_bonus";
    private const STAT_TURN_NBR   = "turns_number";
    private const STAT_TILES_LEFT = "tiles_left";

    private const RUN = "Run";
    private const SET = "Set";

    private const RUN_SET_DIRECTIONS = [
        self::RUN => [ 1, -1 ],
        self::SET => [ 0 ]
    ];

    private const RUN_SET_TYPES = [
        self::RUN => [
            3 => self::RUN_OF_3,
            4 => self::RUN_OF_4,
            5 => self::RUN_OF_5,
            6 => self::RUN_OF_6
        ],
        self::SET => [
            3 => self::SET_OF_3,
            4 => self::SET_OF_4
        ]
    ];

    private const COMBINATIONS = [
        self::SUM_OF_7  => [ "description" => "Sum of 7",  "minutes" => 20, "statistic" => self::STAT_SUM_OF_7 ],
        self::SUM_OF_24 => [ "description" => "Sum of 24", "minutes" => 40, "statistic" => self::STAT_SUM_OF_24 ],
        self::RUN_OF_3  => [ "description" => "Run of 3",  "minutes" => 30, "statistic" => self::STAT_RUN_OF_3 ],
        self::RUN_OF_4  => [ "description" => "Run of 4",  "minutes" => 40, "statistic" => self::STAT_RUN_OF_4 ],
        self::RUN_OF_5  => [ "description" => "Run of 5",  "minutes" => 50, "statistic" => self::STAT_RUN_OF_5 ],
        self::RUN_OF_6  => [ "description" => "Run of 6",  "minutes" => 60, "statistic" => self::STAT_RUN_OF_6 ],
        self::SET_OF_3  => [ "description" => "Set of 3",  "minutes" => 50, "statistic" => self::STAT_SET_OF_3 ],
        self::SET_OF_4  => [ "description" => "Set of 4",  "minutes" => 60, "statistic" => self::STAT_SET_OF_4 ],
        self::BONUS     => [ "description" => "Bonus",     "minutes" => 60, "statistic" => self::STAT_BONUS ]
    ];

    private const STATS = [
        self::STAT_TURN_NBR,
        self::STAT_SUM_OF_7,
        self::STAT_SUM_OF_24,
        self::STAT_RUN_OF_3,
        self::STAT_RUN_OF_4,
        self::STAT_RUN_OF_5,
        self::STAT_RUN_OF_6,
        self::STAT_SET_OF_3,
        self::STAT_SET_OF_4,
        self::STAT_BONUS,
        self::STAT_TILES_LEFT
    ];

	function __construct( )
	{
        // Your global variables labels:
        //  Here, you can assign labels to global variables you are using for this game.
        //  You can use any number of global variables with IDs between 10 and 99.
        //  If your game has options (variants), you also have to associate here a label to
        //  the corresponding ID in gameoptions.inc.php.
        // Note: afterwards, you can get/set the global variables with getGameStateValue/setGameStateInitialValue/setGameStateValue
        parent::__construct();

        self::initGameStateLabels( array(
            //    "my_first_global_variable" => 10,
            //    "my_second_global_variable" => 11,
            //      ...
            //    "my_first_game_variant" => 100,
            //    "my_second_game_variant" => 101,
            //      ...
        ) );

	    $this->tiles = self::getNew( "module.common.deck" );
	    $this->tiles->init( "tile" );

	}

    protected function getGameName( )
    {
		// Used for translations and stuff. Please do not modify.
        return "twentyfourseven";
    }

    /*
        setupNewGame:

        This method is called only once, when a new game is launched.
        In this method, you must setup the game according to the game rules, so that
        the game is ready to be played.
    */
    protected function setupNewGame( $players, $options = array() )
    {
        // Set the colors of the players with HTML color code
        // The default below is red/green/blue/orange/brown
        // The number of colors defined here must correspond to the maximum number of players allowed for the gams
        $gameinfos = self::getGameinfos();
        $default_colors = $gameinfos['player_colors'];

        // Create players
        // Note: if you added some extra field on "player" table in the database (dbmodel.sql), you can initialize it there.
        $sql = "INSERT INTO player (player_id, player_color, player_canal, player_name, player_avatar) VALUES ";

        $values = array();
        foreach( $players as $player_id => $player )
        {
            $color = array_shift( $default_colors );
            $values[] = "('".$player_id."','$color','".$player['player_canal']."','".addslashes( $player['player_name'] )."','".addslashes( $player['player_avatar'] )."')";
        }
        unset( $player );

        $sql .= implode( $values, ',' );
        self::DbQuery( $sql );
        if ($gameinfos['favorite_colors_support']) 
            self::reattributeColorsBasedOnPreferences( $players, $gameinfos['player_colors'] );
        self::reloadPlayersBasicInfos();

        /************ Start the game initialization *****/

        // Init global values with their initial values
        //self::setGameStateInitialValue( 'my_first_global_variable', 0 );

        // Init game statistics
        // (note: statistics used in this file must be defined in your stats.inc.php file)
        //self::initStat( 'table', 'table_teststat1', 0 );    // Init a table statistics
        //self::initStat( 'player', 'player_teststat1', 0 );  // Init a player statistics (for all players)

        // Table stats
        self::initStat( 'table', 'turns_number', 0 );

        // Player stats
        foreach( self::STATS as $stat )
        {
            self::initStat( 'player', $stat, 0 );
        }
        unset( $stat );

        // Create tiles
        $tiles = array();
        for( $value = 1; $value <= 10; $value++ ) 
        {
            $tiles[] = [ 'type' => 'tile', 'type_arg' => $value, 'nbr' => 4 ];
        }
        $this->tiles->createCards( $tiles, 'deck' );

        // Shuffle tiles
        $this->tiles->shuffle('deck');

        // Draw tile for center of board
        $center_tile = $this->tiles->pickCardForLocation('deck', 'board');

        // Draw and discard 3 tiles
        $this->tiles->pickCardsForLocation(3, 'deck', 'discard');

        // Draw player hands
        $players = self::loadPlayersBasicInfos();
        $player_count = count($players);
        $hand_size = ($player_count == 2) ? 6 : 5;

        foreach ( $players as $player_id => $player ) {
            $this->tiles->pickCards($hand_size, 'deck', $player_id);
            self::setStat( $hand_size, self::STAT_TILES_LEFT, $player_id );
        }
        unset( $player );

        // Init the board
        $sql = "INSERT INTO board (board_x,board_y,board_value) VALUES ";
        $sql_values = array();
        for( $x=1; $x<=7; $x++ )
        {
            for( $y=1; $y<=7; $y++ )
            {
                $board_value = "NULL";
                if( $x==4 && $y==4 )  // Center space
                    $board_value = $center_tile['type_arg'];

                $sql_values[] = "('$x','$y',$board_value)";
            }
        }
        $sql .= implode( ',', $sql_values );
        self::DbQuery( $sql );

        // Activate first player (which is in general a good idea :) )
        $this->activeNextPlayer();

        /************ End of the game initialization *****/
    }

    /*
        getAllDatas:

        Gather all informations about current game situation (visible by the current player).

        The method is called each time the game interface is displayed to a player, ie:
        _ when the game starts
        _ when a player refreshes the game page (F5)
    */
    protected function getAllDatas()
    {
        $result = array();

        $current_player_id = self::getCurrentPlayerId();    // !! We must only return informations visible by this player !!

        // Get information about players
        // Note: you can retrieve some extra field you added for "player" table in "dbmodel.sql" if you need it.
        $sql = "SELECT player_id id, player_score score FROM player ";
        $result['players'] = self::getCollectionFromDb( $sql );

        // Tiles in player hand
        $result['hand'] = $this->tiles->getPlayerHand( $current_player_id );
        // Pieces (tiles and time out stones) on the board
        $result['board'] = self::getObjectListFromDB( "SELECT board_x x, board_y y, board_value value
                                                       FROM board
                                                       WHERE board_value IS NOT NULL" );
        // Tallies for both players stats
        $result['tallies'] = self::getPlayersTally();

        // Tile counts by player
        $result['hand_sizes'] = self::getHandSizes();

        // Tile count in deck
        $result['deck_size'] = $this->tiles->countCardInLocation( 'deck' );

        return $result;
    }

    /*
        getPlayersTally:

        Gather all the players stats.
        Place the stats data into an array of arrays keyed first by player id and then stat.

        The method is called:
        - in the getAllDatas function
        - in the newScores notif
    */
    protected function getPlayersTally()
    {
        $result = array();
        $player_stats = array();
        
        // Get player ids
        $player_ids =  array_keys($this->loadPlayersBasicInfos());

        foreach( $player_ids as $player_id )
        {
            foreach( self::STATS as $stat )
            {
                $player_stats[$stat] = self::getStat( $stat, $player_id );
            }
            $result[$player_id] = $player_stats;
            unset( $stat );
        }
        unset( $player_id );

        return $result;
    }

    /*
        getGameProgression:

        Compute and return the current game progression.
        The number returned must be an integer beween 0 (=the game just started) and
        100 (= the game is finished or almost finished).

        This method is called each time we are in a game state with the "updateGameProgression" property set to true
        (see states.inc.php)
    */
    function getGameProgression()
    {
        /*
            Playables - Empty spaces adjacent to tiles on the board (value > 0)
            are playable. If none exist, the game is over and progression is 100.
        */
        $playables = self::getPlayables();
        if( count( $playables ) == 0 ) return 100;

        /*
            Playable tiles - Tiles in players hands are playable when every line
            through a playable space will add up to 24 or less after playing the
            tile. If all players hands are empty or none of their tiles can be
            played (because they are too large), the game is over and progression is 100.
        */
        if( ! self::doesPlayableTileExist() ) return 100;

        /*
            Game progression is a function of the number of tiles played versus
            the number of spaces available to play. At most, 37 tiles will be
            played on the board so 1 metric is to get the percentage of tiles
            played. Another metric is to get the percentage of spaces filled
            on the board.
        */
        $tiles_played = $this->tiles->countCardInLocation( "board" );
        $spaces_filled = self::getUniqueValueFromDb( "SELECT COUNT(board_value) FROM board WHERE board_value IS NOT NULL" );

        // 37 possible tile plays (3 are discarded at the beginning)
        $tile_progression = intdiv( ($tiles_played * 100), 37 );
        // 49 possible spaces to play on (some get time out stones)
        $board_progression = intdiv( ($spaces_filled * 100), 49 );

        // Return the larger value. Tile progression will be used earlier in the game and board progression later in the game.
        return $tile_progression > $board_progression ? $tile_progression : $board_progression;
    }


//////////////////////////////////////////////////////////////////////////////
//////////// Utility functions
////////////

    /*
        Determine whether $value can be played at ($x, $y).
    */
    function canPlayValueAtSpace( $x, $y, $value )
    {
        // If the max sum is between 1 and 24, valid tile play.
        $max = self::maxSumAtSpace( $x, $y, $value );
        return $max < 1 || $max > 24 ? false : true;
    }

    /*
     * Determine whether a playable tile exists for the player or all players
     * if player id is null
     */
    function doesPlayableTileExist( $player_id = null )
    {
        /*
            When no player id is provided, get all the player ids. Iterate
            over the player ids (or single player id if one is provided) and 
            determine if any playable tile exists.
            - If the player is a zombie, skip the playable check.
            - If the player is not a zombie, get their smallest tile and 
            check whether it can be played anywhere on the board.
            - When a playable tile is found, break out of all the loops and 
            return the result.
        */

        $playable_tile_exists = false;

        $player_ids = ($player_id == null ? array_keys($this->loadPlayersBasicInfos()) : [ $player_id ]); 
        foreach( $player_ids as $p_id )
        {
            if( !self::isPlayerZombie( $p_id ) )
            {
                // Get the tiles in the hand of the player (null == all players)
                $tiles_in_hand = $this->tiles->getCardsInLocation( "hand", $p_id, "card_type_arg" );

                if( count( $tiles_in_hand ) > 0 )
                {
                    // The smallest tile in hand is the first element of the array since it is sorted by card_type_arg.
                    $smallest_tile_value = $tiles_in_hand[ 0 ][ "type_arg" ];
                    // See if this tile can be played on the board
                    $playables = self::getPlayables();
                    foreach( $playables as ["x" => $x, "y" => $y, "max" => $max ] )
                    {
                        if( $smallest_tile_value <= $max )
                        {
                            $playable_tile_exists = true;
                            break 2; // break the playables and player_ids loops
                        }
                    }
                    unset( $x, $y, $max );
                } // Else no tiles. This player has nothing to play.
            }
        }
        unset( $p_id );

        return $playable_tile_exists;
    }

    /*
        Find the combos at (x,y)
    */
    function findCombos( $x, $y )
    {
        $combos = array();

        $lines = self::getLinesAtSpace( $x, $y );
        foreach( $lines as $line )
        {
            $length = count( $line );
            // Only score lines with 2 or more spaces
            if( $length > 1) {
                // Sum of combos
                $sum = 0;
                foreach( $line as $space )
                {
                    $sum += $space['value'];
                }
                unset( $space );

                if( $sum == 7 ) // Sum of 7 Combo
                {
                    $combo = [
                        'description' => self::COMBINATIONS[ self::SUM_OF_7 ][ 'description' ],
                        'minutes' => self::COMBINATIONS[ self::SUM_OF_7 ][ 'minutes' ],
                        'tiles' => $line
                    ];
                    $combos[ self::SUM_OF_7 ][] = $combo;
                    unset( $combo );
                }
                if( $sum == 24 ) // Sum of 24 Combo
                {
                    $combo = [
                        'description' => self::COMBINATIONS[ self::SUM_OF_24 ][ 'description' ],
                        'minutes' => self::COMBINATIONS[ self::SUM_OF_24 ][ 'minutes' ],
                        'tiles' => $line
                    ];
                    $combos[ self::SUM_OF_24 ][] = $combo;
                    unset( $combo );
                }
                if( $sum == 24 && $length == 7 )
                {
                    $combo = [
                        'description' => self::COMBINATIONS[ self::BONUS ][ 'description' ],
                        'minutes' => self::COMBINATIONS[ self::BONUS ][ 'minutes' ],
                        'tiles' => $line
                    ];
                    $combos[ self::BONUS ][] = $combo;
                    unset( $combo );
                }

                // Get the runs and sets for this line that include (x,y)
                $runs_and_sets = self::runsAndSets( $x, $y, $line );

                // Add any runs and sets to the combos
                foreach( $runs_and_sets as $type => $items )
                {
                    foreach( $items as $item )
                    {
                        $combo = [
                            'description' => self::COMBINATIONS[ $type ][ 'description' ],
                            'minutes' => self::COMBINATIONS[ $type ][ 'minutes' ],
                            'tiles' => $item
                        ];
                        $combos[ $type ][] = $combo;
                        unset( $combo );
                    }
                    unset( $item );
                }
                unset( $items );
                unset( $type );
            }

        }
        unset( $line );

        /*
            24/7 Bonus Combos

            The bonus combos are each sum of 24 plus each sum of 7 that were
            found. So 1 24 and 1 7 would be 1 bonus; 2 24s and 1 7 would be
            2 bonus combos; 2 24s and 2 7s would be 4 bonus combos, etc.
        */
        $combo7s = array_key_exists( self::SUM_OF_7, $combos ) ? $combos[ self::SUM_OF_7 ] : array();
        $combo24s = array_key_exists( self::SUM_OF_24, $combos ) ? $combos[ self::SUM_OF_24 ] : array();
        foreach( $combo7s as $combo7 )
        {
            foreach( $combo24s as $combo24 )
            {
                $tiles = $combo24[ 'tiles' ];
                array_push( $tiles, ...$combo7[ 'tiles' ] );
                $combo = [
                    'description' => self::COMBINATIONS[ self::BONUS ][ 'description' ],
                    'minutes' => self::COMBINATIONS[ self::BONUS ][ 'minutes' ],
                    'tiles' => $tiles
                ];
                $combos[ self::BONUS ][] = $combo;
                unset( $tiles, $combo );
            }
            unset( $combo24 );
        }
        unset( $combo7 );

        return $combos;
    }

    /*
        Return an array keyed on player_id of each player's hand size. This 
        helper function is used because countCardsByLocationArgs doesn't 
        include a player once they no longer have any tiles in hand.
    */
    protected function getHandSizes()
    {
        $hand_sizes = $this->tiles->countCardsByLocationArgs( 'hand' );

        if( ! is_array( $hand_sizes ) )
        {
            $hand_sizes = array();
        }
        
        // Get player ids
        $player_ids =  array_keys($this->loadPlayersBasicInfos());

        foreach( $player_ids as $player_id )
        {
            if( ! isset( $hand_sizes[ $player_id ] ) )
            { // Any player not in hand_sizes has run out of tiles
                $hand_sizes[ $player_id ] = 0;
            }
        }
        unset( $player_id );

        return $hand_sizes;
    }

    /*
     * Get the lines at (x,y). Each line is composed of the target space (x,y) 
     * and any adjacent spaces filled with a tile (value > 0).
     */
    function getLinesAtSpace( $x, $y )
    {
        $lines = array();
        $lines[0] = $this->getHLineAtSpace( $x, $y );
        $lines[1] = $this->getVLineAtSpace( $x, $y );
        $lines[2] = $this->getLDLineAtSpace( $x, $y );
        $lines[3] = $this->getRDLineAtSpace( $x, $y );
        return $lines;
    }

    /*
     * Gets the horizontal line at (x,y). The line is the target space (x,y) 
     * plus any adjacent spaces filled with a tile (value > 0).
     */
    function getHLineAtSpace( $x, $y )
    {
        self::DbQuery( "SET @x := $x, @y := $y" );
        return self::getObjectListFromDB( "SELECT B.board_x x, B.board_y y, B.board_value value
                                            FROM (
                                                SELECT 
                                                    board_x, 
                                                    board_y, 
                                                    board_value, 
                                                    @exp := @exp + 1 exp, 
                                                    CASE WHEN (board_value > 0 OR (board_x = @x AND board_y = @y)) THEN @act := @act + 1 ELSE @act END act
                                                FROM board b join (SELECT @exp := 0, @act := 0) c
                                                WHERE board_y = @y
                                                ORDER BY board_x, board_y
                                            ) B
                                            JOIN (
                                                SELECT COUNT(*) tep FROM board WHERE board_x <= @x AND board_y = @y
                                            ) E
                                            JOIN (
                                                SELECT COUNT(*) tap FROM board WHERE board_x <= @x AND board_y = @y AND (board_value > 0 OR (board_x = @x AND board_y = @y))
                                            ) A
                                            WHERE
                                                (B.board_value > 0 OR (B.board_x = @x AND B.board_y = @y)) AND -- Space has a tile or is the target space
                                                E.tep > 0 AND A.tap > 0 AND -- Tile expected and actual positions exist
                                                (B.exp - E.tep) = (B.act - A.tap) -- Relative expected position == Actual expected position
                                            ORDER BY B.board_x, B.board_y " );
    }

    /*
     * Gets the vertical line at (x,y). The line is the target space (x,y) 
     * plus any adjacent spaces filled with a tile (value > 0).
     */
    function getVLineAtSpace( $x, $y )
    {
        self::DbQuery( "SET @x := $x, @y := $y" );
        return self::getObjectListFromDB( "SELECT B.board_x x, B.board_y y, B.board_value value
                                            FROM (
                                                SELECT 
                                                    board_x, 
                                                    board_y, 
                                                    board_value, 
                                                    @exp := @exp + 1 exp, 
                                                    CASE WHEN (board_value > 0 OR (board_x = @x AND board_y = @y)) THEN @act := @act + 1 ELSE @act END act
                                                FROM board b join (SELECT @exp := 0, @act := 0) c
                                                WHERE board_x = @x
                                                ORDER BY board_x, board_y
                                            ) B
                                            JOIN (
                                                SELECT COUNT(*) tep FROM board WHERE board_x = @x AND board_y <= @y
                                            ) E
                                            JOIN (
                                                SELECT COUNT(*) tap FROM board WHERE board_x = @x AND board_y <= @y AND (board_value > 0 OR (board_x = @x AND board_y = @y))
                                            ) A
                                            WHERE
                                                (B.board_value > 0 OR (B.board_x = @x AND B.board_y = @y)) AND -- Space has a tile or is the target space
                                                E.tep > 0 AND A.tap > 0 AND -- Tile expected and actual positions exist
                                                (B.exp - E.tep) = (B.act - A.tap) -- Relative expected position == Actual expected position
                                            ORDER BY B.board_x, B.board_y " );
    }

    /*
     * Gets the left diagonal (NW->SE) line at (x,y). The line is the target space (x,y) 
     * plus any adjacent spaces filled with a tile (value > 0).
     */
    function getLDLineAtSpace( $x, $y )
    {
        self::DbQuery( "SET @x := $x, @y := $y" );
        return self::getObjectListFromDB( "SELECT B.board_x x, B.board_y y, B.board_value value
                                            FROM (
                                                SELECT 
                                                    board_x, 
                                                    board_y, 
                                                    board_value, 
                                                    @exp := @exp + 1 exp, 
                                                    CASE WHEN (board_value > 0 OR (board_x = @x AND board_y = @y)) THEN @act := @act + 1 ELSE @act END act
                                                FROM board b join (SELECT @exp := 0, @act := 0) c
                                                WHERE board_x = (@x - @y) + board_y AND board_y = (@y - @x) + board_x
                                                ORDER BY board_x, board_y
                                            ) B
                                            JOIN (
                                                SELECT COUNT(*) tep FROM board WHERE board_x = (@x - @y) + board_y AND board_y = (@y - @x) + board_x AND board_x <= @x AND board_y <= @y
                                            ) E
                                            JOIN (
                                                SELECT COUNT(*) tap FROM board WHERE board_x = (@x - @y) + board_y AND board_y = (@y - @x) + board_x AND board_x <= @x AND board_y <= @y AND (board_value > 0 OR (board_x = @x AND board_y = @y))
                                            ) A
                                            WHERE
                                                (B.board_value > 0 OR (B.board_x = @x AND B.board_y = @y)) AND -- Space has a tile or is the target space
                                                E.tep > 0 AND A.tap > 0 AND -- Tile expected and actual positions exist
                                                (B.exp - E.tep) = (B.act - A.tap) -- Relative expected position == Actual expected position
                                            ORDER BY B.board_x, B.board_y " );
    }

    /*
     * Gets the right diagonal (SW->NE) line at (x,y). The line is the target space (x,y) 
     * plus any adjacent spaces filled with a tile (value > 0).
     */
    function getRDLineAtSpace( $x, $y )
    {
        self::DbQuery( "SET @x := $x, @y := $y") ;
        return self::getObjectListFromDB( "SELECT B.board_x x, B.board_y y, B.board_value value
                                            FROM (
                                                SELECT 
                                                    board_x, 
                                                    board_y, 
                                                    board_value, 
                                                    @exp := @exp + 1 exp, 
                                                    CASE WHEN (board_value > 0 OR (board_x = @x AND board_y = @y)) THEN @act := @act + 1 ELSE @act END act
                                                FROM board b JOIN (SELECT @exp := 0, @act := 0) c
                                                WHERE board_x = (@x + @y) - board_y AND board_y = (@x + @y) - board_x
                                                ORDER BY board_x, board_y
                                            ) B
                                            JOIN (
                                                SELECT COUNT(*) tep FROM board WHERE board_x = (@x + @y) - board_y AND board_y = (@x + @y) - board_x AND board_x <= @x AND board_y >= @y
                                            ) E
                                            JOIN (
                                                SELECT COUNT(*) tap FROM board WHERE board_x = (@x + @y) - board_y AND board_y = (@x + @y) - board_x AND board_x <= @x AND board_y >= @y AND (board_value > 0 OR (board_x = @x AND board_y = @y))
                                            ) A
                                            WHERE
                                                (B.board_value > 0 OR (B.board_x = @x AND B.board_y = @y)) AND -- Space has a tile or is the target space
                                                E.tep > 0 AND A.tap > 0 AND -- Tile expected and actual positions exist
                                                (B.exp - E.tep) = (B.act - A.tap) -- Relative expected position == Actual expected position
                                            ORDER BY B.board_x, B.board_y " );
    }

    /*
        Get the playable spaces on the board and the max tile value that can 
        be played.
    */
    function getPlayables()
    {
        // Get the playable spaces: empty spaces adjacent to a tile (value > 0)
        $playables = self::getObjectListFromDB( "SELECT E.board_x x, E.board_y y
                                                FROM board E
                                                JOIN board A ON
                                                    E.board_value IS NULL AND
                                                    A.board_value IS NOT NULL AND A.board_value > 0 AND
                                                    A.board_x BETWEEN (E.board_x - 1) AND (E.board_x + 1) AND
                                                    A.board_y BETWEEN (E.board_y - 1) AND (E.board_y + 1)
                                                GROUP BY E.board_x, E.board_y, E.board_value
                                                ORDER BY E.board_x, E.board_y " );
        
        // For each space, calculate the largest tile that can be played.
        foreach( $playables as $i => ["x" => $x, "y" => $y] )
        {
            // Get the max sum if a 1 were played
            $sum = self::maxSumAtSpace( $x, $y, 1 );
            // Calculate the room left on the space (25 - sum)
            $room = 25 - $sum;
            // Max tile is 10 (room > 10) or room
            $playables[ $i ][ "max" ] = ( $room > 10 ? 10 : $room );
        }
        unset( $i, $x, $y );

        return $playables;
    }

    /*
        Determine whether a space is double time.
    */
    function isDoubleTime( $x, $y )
    {
        // Only spaces between (1,1) and (7,7) can be double time
        if ( $x < 1 || $x > 7 || $y < 1 || $y > 7 ) return false;

        // Translate coords to serial location (1..49)
        $loc = ( ( $x - 1 ) * 7 ) + $y;

        /*
            A double time space is any serial location that is a multiple of 5
            (5, 10, 15, etc) except the center space (25).
        */
        return ( ( $loc % 5 ) == 0 ) && ( ( $loc % 25 ) != 0 );
    }

    /*
        Checks whether the player is a zombie (kicked or quit)
    */
    protected function isPlayerZombie( $player_id ) 
    {
        $players = self::loadPlayersBasicInfos();
        if ( ! isset($players[$player_id]) )
            throw new BgaSystemException("Player $player_id is not playing here");
        
        return ($players[$player_id]['player_zombie'] == 1);
    }

    /*
        Block any playable space with a time out stone if playing a tile with
        the value of 1 would result in an invalid line (sum > 24).
    */
    function markTimeOuts()
    {
        $time_outs = array();

        $playables = self::getPlayables();
        foreach( $playables as ["x" => $x, "y" => $y, "max" => $max] )
        {
            if( $max < 1 )
            {
                self::DbQuery( "UPDATE board SET board_value = 0 WHERE board_x = $x AND board_y = $y" );
                $time_outs[] = [ "x" => $x, "y" => $y, "value" => 0 ];
            }
        }
        unset( $x, $y, $max );

        return $time_outs;
    }

    /*
        Find the max sum at (x,y) assuming tile is played. If the board 
        position or the tile value is not valid, 0 is returned. If the board
        position is filled (non-null), 0 is returned. Otherwise, the largest 
        sum of the lines passing through (x,y) is returned.
    */
    function maxSumAtSpace( $x, $y, $value )
    {
        // Can't play if not on the board and not a tile
        if( $x < 1 || $x > 7 || $y < 1 || $y > 7 || $value < 1 || $value > 10 ) return 0;

        $board_value = self::getUniqueValueFromDb( "SELECT board_value FROM board WHERE board_x = $x AND board_y = $y" );

        // Can't play if the space has something in it
        if( ! is_null( $board_value ) ) return 0;

        // Get the lines at (x,y)
        $lines = self::getLinesAtSpace( $x, $y );

        // Sum each line
        $largest_sum = 0;
        foreach( $lines as $line )
        {
            $current_sum = 0;
            foreach( $line as $space )
            {
                /*
                    Add up the values on the spaces. For the target space,
                    use the value passed in since the target space is empty.
                */
                $current_sum += ($space['x'] == $x && $space['y'] == $y) ? $value : $space['value'];
            }
            unset( $space );

            if ($current_sum > $largest_sum) $largest_sum = $current_sum;
        }
        unset( $line );

        // Return the largest sum
        return $largest_sum;
    }

    /*
        Find the runs and sets in a line. Any run or set found must contain
        the space (x,y) where the tile was played.
    */
    private function runsAndSets( $x, $y, $line )
    {

        $possible = array(); // Empty array to accumulate the next possible run or set
        $has_played_space = false;
        $combos = array();

        foreach( self::RUN_SET_DIRECTIONS as $type => $directions )
        {

            foreach( $directions as $direction )
            {

                foreach( $line as $space )
                {

                    $last_key = array_key_last( $possible );
                    if( ! is_null( $last_key ) && ( ( $space['value'] - $possible[$last_key]['value'] ) != $direction ) )
                    {
                        /*
                            The possible has at least 1 element and the
                            difference between the value of the current space
                            and the last space in the possible is not the same
                            as the direction (i.e., 1 or -1 for runs and 0 for
                            sets). Check if the possible is a result and
                            start a new possible to accumulate the current
                            space.
                        */
                        $length = count( $possible );
                        if( $length > 2 && $has_played_space )
                        {
                            /*
                                The possible is the correct length for a run or
                                set (i.e., 3 or more) and contains the space
                                where the tile was played (x, y). Copy the
                                possible to the results.
                            */
                            $combo_type = self::RUN_SET_TYPES[ $type ][ $length ];
                            $combo = $possible;
                            $combos[ $combo_type ][] = $combo;
                            unset( $combo );
                            unset( $combo_type );
                        }
                        unset( $length );

                        /*
                            The possible was either copied to the result or
                            is not valid. Create a new possible and clear the
                            has played space flag.
                        */
                        $possible = array();
                        $has_played_space = false;
                    }

                    /*
                        Either the current possible hasn't ended or a new
                        possible has been created because the current space
                        didn't match the direction needed to continue the
                        possible. In either case add the space to the
                        possible.
                    */
                    $possible[] = $space;
                    if( $space['x'] == $x && $space['y'] == $y )
                    {
                        $has_played_space = true;
                    }

                }
                unset( $space );

                /*
                    Finished iterating over the line for the current type and
                    direction. Check the possible array to see whether it
                    should be added to results.
                */
                $length = count( $possible );
                if( $length > 2 && $has_played_space )
                {
                    /*
                        The possible is the correct length for a run or
                        set (i.e., 3 or more) and contains the space
                        where the tile was played (x, y). Copy the
                        possible to the results.
                    */
                    $combo_type = self::RUN_SET_TYPES[ $type ][ $length ];
                    $combo = $possible;
                    $combos[ $combo_type ][] = $combo;
                    unset( $combo );
                    unset( $combo_type );
                }
                unset( $length );

                /*
                    Will start a new type and direction iteration if any are
                    left. Create a new possible and clear the has played space
                    flag.
                */
                $possible = array();
                $has_played_space = false;

            }
            unset( $direction );
        }
        unset( $directions );

        return $combos;
    }

    /*
        Score the space
    */
    function scoreSpace( $x, $y )
    {
        /*
            Tally

            The tally is an array indexed by the combination key (i.e., 
            self::SUM_OF_7, etc).
        */
        $tally = array();

        /*
            Double Time

            When the space being scored (i.e., the position on the board where
            the tile was played) is a double time space, set the double time 
            factor to 2.
        */
        $double_time_factor = ( self::isDoubleTime( $x, $y ) ? 2 : 1 );

        /*
            Get the combos at (x,y) and tally each combo
        */
        $combos = self::findCombos( $x, $y );
        foreach( $combos as $type => $items )
        {
            $tally[ $type ] = count( $items ) * $double_time_factor;
            /*
                Apply the double time factor to the minutes on each combo scored
            */
            foreach( $items as $i => ['description' => $description, 'minutes' => $minutes, 'tiles' => $tiles] )
            {
                $combos[$type][$i]['minutes'] = $minutes * $double_time_factor;
                $combos[$type][$i]['description'] = $description . ($double_time_factor > 1 ? " x$double_time_factor" : "");
            }
            unset( $i, $description, $minutes, $tiles );
        }
        unset( $type, $items );

        /*
            Calculate minutes

            Minutes are simply the sum of the tallies multiplied by their
            minute values (Sum of 7 == 20, Sum of 24 == 40, etc).
        */
        $minutes = 0;
        foreach( $tally as $type => $tally_count )
        {
            $minutes += $tally_count * self::COMBINATIONS[ $type ][ 'minutes' ];
        }
        unset( $type, $tally_count );

        $score = array();
        $score['tally'] = $tally;
        $score['minutes'] = $minutes;
        $score["combos"] = $combos;

        return $score;
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Player actions
////////////

    /*
        Each time a player is doing some game action, one of the methods below is called.
        (note: each method below must match an input method in twentyfourseven.action.php)
    */

    /*
     * Play the given tile on the given space (x,y)
     */
    function playTile( $x, $y, $played_tile_id )
    {
        /*
         * Check that this player is active and that this action is possible
         * at this moment.
         */
        self::checkAction( 'playTile' );

        $player_id = self::getActivePlayerId();

        /*
         * Check if the tile is a valid play. The play is valid if:
         * - The space is empty (null).
         * - The tile is in the active player's hand.
         * - The sum of any line passing through the space sums to 24 or less
         * after playing the tile.
         */

        $played_tile = $this->tiles->getCard( $played_tile_id );
        $played_tile_value = $played_tile[ "type_arg" ];
        $played_tile_location = $played_tile[ "location" ];
        $played_tile_location_arg = $played_tile[ "location_arg" ];

        $can_play_tile = self::canPlayValueAtSpace( $x, $y, $played_tile_value );
        if ( $played_tile_location == "hand" &&
            $played_tile_location_arg == $player_id &&
            $can_play_tile )
        {
            // This move is possible!
            $played = array();
            $played[] = [ "x" => $x, "y" => $y, "value" => $played_tile_value ];

            /*
             * Update the board at (x,y) with the value of the tile
             */
            self::DbQuery( "UPDATE board SET board_value = $played_tile_value WHERE board_x = $x AND board_y = $y" );

            /*
             * Change the location of the tile from the player's hand to
             * the board.
             */
            $this->tiles->moveCard( $played_tile_id, 'board' );

            /*
             * Mark any playable spaces with time out stones (value = 0) that
             * are no longer playable (placing any tile on the space would
             * result in a line through the space adding up to more than 24).
             */
            $time_outs = self::markTimeOuts();

            /*
                Score the space. Get all the lines passing through the space
                (x,y) and tally the score.
            */
            $score = self::scoreSpace( $x, $y );

            /*
             * Update the player score. Add the total scored from playing the
             * tile to the player's score.
             */
            $minutes = $score['minutes'];
            self::DbQuery( "UPDATE player
                            SET player_score = player_score + $minutes
                            WHERE player_id = $player_id");

            /*
             * Update the statistics for the player. Increase the tallies for
             * the player based on what was scored (runs, sets, 24/7s, etc)
             * from playing the tile.
             */

            // Table stats
            self::incStat( 1, self::STAT_TURN_NBR );

            // Player stats
            self::incStat( 1, self::STAT_TURN_NBR, $player_id );
            foreach( $score[ 'tally' ] as $type => $tally_count )
            {
                self::incStat( $tally_count, self::COMBINATIONS[ $type ][ 'statistic' ], $player_id );
            }
            unset( $type, $tally_count );

            /*
             * Create a string of the combos the player scored if any
             */
            $scoring_combos = "";
            if ($minutes > 0) {
                $num_combos_scored = 0;
                $scoring_combos = "(";
                // at least one scorable combo was played
                foreach( $score[ "tally" ] as $type => $tally_count )
                {
                    if( $tally_count > 0 ) 
                    {   
                        $combo_scored = "";
                        if( $num_combos_scored > 0 )
                        {
                            $combo_scored .= ", ";
                        }
                        $combo_description = self::COMBINATIONS[ $type ][ "description" ];
                        $combo_scored .= $combo_description . " x " . $tally_count;
                        $scoring_combos .= $combo_scored;
                        $num_combos_scored++;
                    }
                }
                unset( $type, $tally_count );
                $scoring_combos .= ")";
            }

            /*
             * Draw a tile and add it to the player's hand.
             */
            $drawTile = $this->tiles->pickCard( 'deck', $player_id );

            /*
                Update player score aux with tile count for tie-breakers
                - Getting the count as a negative number so a smaller tile 
                is a 'larger' number since the tie-breaker is the player 
                with the fewest tiles among tied players.
            */
            $tile_count = $this->tiles->countCardInLocation( 'hand', $player_id );
            self::setStat( $tile_count, self::STAT_TILES_LEFT, $player_id );
            self::DbQuery( "UPDATE player
                            SET player_score_aux = (0 - $tile_count)
                            WHERE player_id = $player_id");


            /*
             * Notify players of the game progression.
             */

            /*
             * Played tile notification
             */
            self::notifyAllPlayers( "playTile", clienttranslate( '${player_name} played a ${value} on column ${x} and row ${y} and scored ${minutes} minutes ${scoring_combos}' ), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName(),
                'minutes' => $minutes,
                'value' => $played_tile_value,
                'x' => $x,
                'y' => $y,
                'scoring_combos' => $scoring_combos,
                'score' => $score,
                'new_pieces' => [...$played, ...$time_outs]
            ) );

            /*
             * New scores notification
             */
            $newScores = self::getCollectionFromDb( "SELECT player_id, player_score FROM player", true );
            $tallies = self::getPlayersTally();
            $hand_sizes = self::getHandSizes();
            $deck_size = $this->tiles->countCardInLocation( 'deck' );
            self::notifyAllPlayers( "newScores", "", array(
                "scores" => $newScores,
                "tallies" => $tallies,
                "hand_sizes" => $hand_sizes,
                "deck_size" => $deck_size
            ) );

            /*
                Hand change notification (only to player playing tile)
            */
            self::notifyPlayer( $player_id, "handChange", "", array(
                "playTile" => $played_tile,
                "drawTile" => $drawTile
            ) );

            // Go to the next state
            $this->gamestate->nextState( 'playTile' );
        } else
            throw new BgaSystemException( self::_( "Impossible move" ) );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state arguments
////////////

    /*
        Here, you can create methods defined as "game state arguments" (see "args" property in states.inc.php).
        These methods function is to return some additional information that is specific to the current
        game state.
    */

    function argPlayerTurn()
    {
        return array(
            'playables' => self::getPlayables()
        );
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Game state actions
////////////

    function stNextPlayer()
    {
        // Active next player
        $player_id = self::activeNextPlayer();

        /*
         * Playables - Empty spaces adjacent to tiles on the board (value > 0)
         * are playable. If none exist, the game is over.
         */
        $playables = self::getPlayables();
        if( count( $playables ) == 0 )
        {
            /*
             * The board has no playable spaces. Game over.
             */
            $this->gamestate->nextState( 'endGame' );
            return ;
        }

        /*
         * Playable tiles - Tiles in players hands are playable when every line
         * through a playable space will add up to 24 or less after playing the
         * tile. If all players hands are empty or none of their tiles can be
         * played (because they are too large), the game is over.
         */
        if( ! self::doesPlayableTileExist() )
        {
            /*
             * No player has a tile that can be played on the board or all
             * players are out of tiles. Game over.
             */
            $this->gamestate->nextState( 'endGame' );
            return ;
        }

        /*
         * Can the active player play a tile?
         */
        if( ! self::doesPlayableTileExist( $player_id ) )
        {   
            /*
             * Played skipped notification
             */
            self::notifyAllPlayers( "cantPlay", clienttranslate( '${player_name} skipped, no playable tiles' ), array(
                'player_id' => $player_id,
                'player_name' => self::getActivePlayerName()
            ) );

            /*
             * This player can't play. Since we are here, we know there are
             * playable spaces on the board and at least one player has a tile
             * that can be played. The game is not over but this player cannot
             * take a turn.
             */
            $this->gamestate->nextState( 'cantPlay' );
        }
        else
        {
            /*
             * This player can play. Give them some extra time
             */
            self::giveExtraTime( $player_id );
            $this->gamestate->nextState( 'nextTurn' );
        }
    }

//////////////////////////////////////////////////////////////////////////////
//////////// Zombie
////////////

    /*
        zombieTurn:

        This method is called each time it is the turn of a player who has quit the game (= "zombie" player).
        You can do whatever you want in order to make sure the turn of this player ends appropriately
        (ex: pass).

        Important: your zombie code will be called when the player leaves the game. This action is triggered
        from the main site and propagated to the gameserver from a server, not from a browser.
        As a consequence, there is no current player associated to this action. In your zombieTurn function,
        you must _never_ use getCurrentPlayerId() or getCurrentPlayerName(), otherwise it will fail with a "Not logged" error message.
    */

    function zombieTurn( $state, $active_player )
    {
    	$statename = $state['name'];

        if ($state['type'] === "activeplayer") {
            switch ($statename) {
                default:
                    $this->gamestate->nextState( "zombiePass" );
                	break;
            }

            return;
        }

        if ($state['type'] === "multipleactiveplayer") {
            // Make sure player is in a non blocking status for role turn
            $this->gamestate->setPlayerNonMultiactive( $active_player, '' );

            return;
        }

        throw new feException( "Zombie mode not supported at this game state: ".$statename );
    }

///////////////////////////////////////////////////////////////////////////////////:
////////// DB upgrade
//////////

    /*
        upgradeTableDb:

        You don't have to care about this until your game has been published on BGA.
        Once your game is on BGA, this method is called everytime the system detects a game running with your old
        Database scheme.
        In this case, if you change your Database scheme, you just have to apply the needed changes in order to
        update the game database and allow the game to continue to run with your new version.

    */

    function upgradeTableDb( $from_version )
    {
        // $from_version is the current version of this game database, in numerical form.
        // For example, if the game was running with a release of your game named "140430-1345",
        // $from_version is equal to 1404301345

        // Example:
//        if( $from_version <= 1404301345 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "ALTER TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        if( $from_version <= 1405061421 )
//        {
//            // ! important ! Use DBPREFIX_<table_name> for all tables
//
//            $sql = "CREATE TABLE DBPREFIX_xxxxxxx ....";
//            self::applyDbUpgradeToAllDB( $sql );
//        }
//        // Please add your future database scheme changes here
//
//


    }
}
