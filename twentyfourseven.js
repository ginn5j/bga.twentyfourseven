/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * 24/7: The Game BGA implementation: © Jim Ginn <ginn5j@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * twentyfourseven.js
 *
 * TwentyFourSeven user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

define([
    "dojo","dojo/_base/declare",
    "ebg/core/gamegui",
    "ebg/counter",
    "ebg/stock"
],
function (dojo, declare) {
    return declare("bgagame.twentyfourseven", ebg.core.gamegui, {
        constructor: function(){
            // Init global variables
            this.tilewidth = 75;
            this.tileheight = 105;
            this.playables = [];
            this.combos = [];
            this.animations = 0;
        },

        /*
            setup:

            This method must set up the game user interface according to current game situation specified
            in parameters.

            The method is called each time the game interface is displayed to a player, ie:
            _ when the game starts
            _ when a player refreshes the game page (F5)

            "gamedatas" argument contains all datas retrieved by your "getAllDatas" PHP method.
        */

        setup: function( gamedatas )
        {
            this.setupTypes();

            this.setupGameSummary( gamedatas );

            this.tf7Dialog = new ebg.popindialog();

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];
                var hand_size = gamedatas.hand_sizes[player_id];

                var player_board_div = $('player_board_'+player_id);
                dojo.place( this.format_block('jstpl_player_board', {
                    player_id: player_id,
                    hand_size: hand_size
                } ), player_board_div );                
            }

            // Player hand
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('tf7_player_hand'), this.tilewidth, this.tileheight);
            this.playerHand.image_items_per_row = 5;
            this.playerHand.centerItems = true;
            this.playerHand.extraClasses = 'tf7_player_tile';
            this.playerHand.setSelectionMode( 1 );
            this.playerHand.setSelectionAppearance( 'class' );
            dojo.connect( this.playerHand, 'onChangeSelection', this, 'onTileSelectionChange' );

            // Create cards types:
            for (var value = 1; value <= 10; value++) {
                // Build card type id
                this.playerHand.addItemType(value, value, g_gamethemeurl + 'img/tiles.png', (value - 1));
            }

            // Update the player's hand
            for ( const i in gamedatas.hand )
            {
                const tile = gamedatas.hand[ i ];
                this.playerHand.addToStockWithId(tile.type_arg, tile.id);
            }

            for( const space of gamedatas.board )
            {
                if( space.value !== null )
                {
                    this.addPieceOnBoard( space.x, space.y, space.value );
                }
            }

            /*
                When an event is handled, the this reference will not be this 
                object. Capture this as a variable so it can be used and 
                passed to the event handler callbacks.
            */
            var self = this;

            // Listen for click events on the board
            document.querySelector( '#board' ).addEventListener( 'click', function( event ) { self.onPlayTile( event, self ); } );

            // Listen for animationend events on the board
            document.querySelector( '#board' ).addEventListener( 'animationend', function( event ) { self.onAnimationEnd( event, self ); } );

            // Setup game notifications to handle (see "setupNotifications" method below)
            this.setupNotifications();
        },

        /*
            setupGameSummary:

            Sets up the game summary panel in the player boards.
        */
        setupGameSummary: function( gamedatas )
        {
            // Deck size
            var deck_size = gamedatas.deck_size;
            document.querySelector( '#tf7_game_summary .tf7_deck_size_value' ).textContent = deck_size;

            // Help dialog
            this.addActionButton( 'tf7_help_button', _('Help'), 'onHelp', 'tf7_game_summary', false, 'gray');
        },

        /*
            setupTypes:

            Sets up types used in the game
        */
        setupTypes: function()
        {
            // Combo Types
            this.comboTypes = {
                "sum-of-7"  : { "name" : _("Sum of 7") , "20" : "",  "40" : " x2" },
                "sum-of-24" : { "name" : _("Sum of 24"), "40" : "",  "80" : " x2" },
                "run-of-3"  : { "name" : _("Run of 3") , "30" : "",  "60" : " x2" },
                "run-of-4"  : { "name" : _("Run of 4") , "40" : "",  "80" : " x2" },
                "run-of-5"  : { "name" : _("Run of 5") , "50" : "", "100" : " x2" },
                "run-of-6"  : { "name" : _("Run of 6") , "60" : "", "120" : " x2" },
                "set-of-3"  : { "name" : _("Set of 3") , "50" : "", "100" : " x2" },
                "set-of-4"  : { "name" : _("Set of 4") , "60" : "", "120" : " x2" },
                "bonus"     : { "name" : _("Bonus")    , "60" : "", "120" : " x2" }
            };
        },
    
        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            switch( stateName )
            {
                case 'playerTurn':
                    this.onEnterPlayerTurn( args );
                    break;

            /* Example:

            case 'myGameState':

                // Show some HTML block at this game state
                dojo.style( 'my_html_block_id', 'display', 'block' );

                break;
           */


            case 'dummmy':
                break;
            }
        },

        // onLeavingState: this method is called each time we are leaving a game state.
        //                 You can use this method to perform some user interface changes at this moment.
        //
        onLeavingState: function( stateName )
        {
            switch( stateName )
            {
            /* Example:

            case 'myGameState':

                // Hide the HTML block we are displaying only during this game state
                dojo.style( 'my_html_block_id', 'display', 'none' );

                break;
           */


            case 'dummmy':
                break;
            }
        },

        onEnterPlayerTurn: function( args )
        {
            this.updatePlayables( args.args.playables );
        },

        onHelp: function() 
        {
            this.tf7Dialog.create( 'tf7_help_dialog' );
            this.tf7Dialog.setTitle( _("24/7 Help") );
            this.tf7Dialog.setContent( this.helpDialogHTML() );
            this.tf7Dialog.show();
        },

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            if( this.isCurrentPlayerActive() )
            {
                switch( stateName )
                {
/*
                 Example:

                 case 'myGameState':

                    // Add 3 action buttons in the action status bar:

                    this.addActionButton( 'button_1_id', _('Button 1 label'), 'onMyMethodToCall1' );
                    this.addActionButton( 'button_2_id', _('Button 2 label'), 'onMyMethodToCall2' );
                    this.addActionButton( 'button_3_id', _('Button 3 label'), 'onMyMethodToCall3' );
                    break;
*/
                }
            }
        },

        ///////////////////////////////////////////////////
        //// Utility methods

        addPieceOnBoard: function( x, y, value, player )
        {
            if (value > 0) { // Tile
                if( document.querySelector( '#tile_'+x+'_'+y ) == null )
                {
                    dojo.place( this.format_block( 'jstpl_tile', {
                        x_y: x+'_'+y,
                        value: value
                    } ) , 'pieces' );
    
                    if (player !== undefined) {
                        this.placeOnObject( 'tile_'+x+'_'+y, 'overall_player_board_'+player );
                    } else {
                        this.placeOnObject( 'tile_'+x+'_'+y, 'board' );
                    }
                    this.slideToObject( 'tile_'+x+'_'+y, 'space_'+x+'_'+y ).play();
                } //else this is the player that played the tile and it was placed when they selected the board space on their turn
            } else { // Time out stone
                dojo.place( this.format_block( 'jstpl_stone', {
                    x_y: x+'_'+y
                } ) , 'pieces' );

                this.placeOnObject( 'stone_'+x+'_'+y, 'board' );
                this.slideToObject( 'stone_'+x+'_'+y, 'space_'+x+'_'+y ).play();
            }
        },

        /*
            Clear the playable spaces and the list of playables.
        */
        clearPlayables: function()
        {
            for( const space of document.querySelectorAll( ".tf7_playable_space" ) )
            {
                this.removeTooltip( space.id );
            }
            dojo.query( '.tf7_playable_space' ).removeClass( 'tf7_playable_space tf7_playable tf7_not_playable fa fa-2x fa-check-circle fa-times-circle' );
            this.playables = [];
        },

        /*
            Creates combo description for display.
        */
        comboDescription: function( type, minutes )
        {
            return this.comboTypes[ type ][ "name" ] + this.comboTypes[ type ][ minutes ];
        },
    
        helpDialogHTML: function()
        {
            return '<div class="tf7_help_html" id="tf7_help_html">' +
                        '<div>' +
                            '<span class="tf7_deck_size">' +
                                '<i class="tf7_deck_size_icon fa fa-shopping-bag" aria-hidden="true"></i>&nbsp;' +
                                '<span class="tf7_deck_size_value">24</span>' +
                            '</span>' +
                            ' : ' + _('Tiles available to draw') +
                        '</div>' +
                        '<div>' +
                            '<span class="tf7_hand_size">' +
                                '<i class="tf7_hand_size_icon fa fa-hand-paper-o" aria-hidden="true"></i>&nbsp;' +
                                '<span class="tf7_hand_size_value">6</span>' +
                            '</span>' +
                            ' : ' + _("Tiles in a player's hand") +
                        '</div>' +
                        '<div>' +
                            '<table class="tf7_combo_summary" id="tf7_combo_summary">' +
                                '<thead>' +
                                    '<tr><th>' + _('Combination') + '</th><th>' + _('Minutes') + '</th></tr>' +
                                '</thead>' +
                                '<tbody>' +
                                    '<tr><td>' + _('Sum of 7 (all tiles, 2+ tiles)') + '</td><td>20</td></tr>' +
                                    '<tr><td>' + _('Run of 3') + '</td><td>30</td></tr>' +
                                    '<tr><td>' + _('Sum of 24 (all tiles)') + '</td><td>40</td></tr>' +
                                    '<tr><td>' + _('Run of 4') + '</td><td>40</td></tr>' +
                                    '<tr><td>' + _('Set of 3 (3 of a Kind)') + '</td><td>50</td></tr>' +
                                    '<tr><td>' + _('Run of 5') + '</td><td>50</td></tr>' +
                                    '<tr><td>' + _('Set of 4 (4 of a Kind)') + '</td><td>60</td></tr>' +
                                    '<tr><td>' + _('Run of 6') + '</td><td>60</td></tr>' +
                                    '<tr><td>' + _('Bonus') + '<br>- ' + _('Sum of 7 and Sum of 24') + '<br>- ' + _('Sum of 24 in 7 Tiles') + '</td><td>60</td></tr>' +
                                '</tbody>' +
                            '</table>' +
                        '</div>' +
                    '</div>';
        },

        highlightCombos: function( args )
        {
            for( const type in args.score.combos )
            {
                for( const combo of args.score.combos[ type ] )
                {
                    this.combos.push({
                        "x" : args.x,
                        "y" : args.y,
                        "player_id" : args.player_id,
                        "description" : this.comboDescription( type, combo.minutes ),
                        "minutes" : combo.minutes,
                        "tiles" : combo.tiles
                    });
                }
            }
            this.combos.reverse();

            // Kick off highlighting the combos
            this.highlightNextCombo();
        },

        highlightNextCombo: function()
        {
            // Clear the animation counter
            this.animations = 0;

            // Remove current highlighting
            dojo.query( ".tf7_highlight_combo" ).removeClass( 'tf7_highlight_combo' );
            dojo.query( ".tf7_combo_score" ).forEach( dojo.destroy );

            // Force a reflow
            document.querySelector( '#board' ).offsetHeight;

            // Pop next combo
            var combo = this.combos.pop();

            // Highlight combo
            if( combo !== undefined )
            {
                for( const tile of combo.tiles )
                {
                    // If the tile doesn't have the highlight, add it (same tile can appear twice on bonus combos)
                    if( !dojo.hasClass( "tile_"+tile.x+"_"+tile.y, "tf7_highlight_combo" ) )
                    {
                        this.animations++;
                        dojo.addClass( "tile_"+tile.x+"_"+tile.y, "tf7_highlight_combo" );
                    }
                }
                var t = "tile_"+combo.x+"_"+combo.y;
                var r = dojo.place(this.format_string('<div class="tf7_combo_score tf7_fade_combo">+${score}<br>${description}</div>', { score: combo.minutes, description: combo.description }), t);
                this.placeOnObject(r, t);
                dojo.style(r, "color", "#" + this.gamedatas.players[combo.player_id]["color"]);
                this.animations++;
            }
        },

        /*
            Handle animation end (highlighting a scored combination)
            Since this will be called from an event handler, we need to pass
            along the game instance when registering the handler.
        */
        onAnimationEnd: function( event, game )
        {
            // Stop propagation and prevent any default handling of the event
            event.stopPropagation();
            event.preventDefault();

            game.animations--;
            if (game.animations == 0) 
            {
                // Start the next combo highlight
                game.highlightNextCombo();
            }
        },
        
        /*
            Indicate whether the selected tile can be played on any of the 
            playable spaces.
        */
        onTileSelectionChange: function( control_name, item_id )
        {
            if( this.isCurrentPlayerActive() )
            {
                // Get the selected tiles (should be 0 or 1)
                var tiles = this.playerHand.getSelectedItems();
            
                if ( tiles.length == 1 )
                {
                    var tile = tiles[0];
                    for( const playable of this.playables )
                    {
                        if( tile.type <= playable.max )
                        {
                            dojo.replaceClass( 'space_'+playable.x+'_'+playable.y, 'tf7_playable fa-check-circle', 'tf7_not_playable fa-times-circle' );
                        }
                        else
                        {
                            dojo.replaceClass( 'space_'+playable.x+'_'+playable.y, 'tf7_not_playable fa-times-circle', 'tf7_playable fa-check-circle' );
                        }
                    }
                }
                else
                {
                    this.playerHand.unselectAll();
                    dojo.query( '.tf7_playable_space' ).removeClass( 'tf7_playable tf7_not_playable fa-check-circle fa-times-circle' );
                }
            }
            else
            {
                this.playerHand.unselectAll();
                this.clearPlayables();
            }
        },

        removePieceFromBoard: function( x, y, value )
        {
            if (value > 0) { // Tile
                dojo.destroy( 'tile_'+x+'_'+y );
            } else { // Time out stone
                dojo.destroy( 'stone_'+x+'_'+y );
            }
        },

        startActionTimer: function(e, t) {
            var n = document.getElementById(e),
                i = null,
                r = n.innerHTML,
                a = t,
                l = function() {
                    var t = document.getElementById(e);
                    if (null == t) window.clearInterval(i);
                    else if (a-- > 1) t.innerHTML = r + " (" + a + ")";
                    else {
                        window.clearInterval(i);
                        t.click()
                    }
                };
            l();
            i = window.setInterval((function() {
                return l()
            }), 1e3);
		},

        /*
            Update the list of playables and show the playable spaces on the 
            board.
        */
        updatePlayables: function( playables )
        {
            this.playables = playables;

            if( this.isCurrentPlayerActive() )
            {
                for( const playable of this.playables )
                {
                    // x,y is a playable space
                    dojo.addClass( 'space_'+playable.x+'_'+playable.y, 'tf7_playable_space fa fa-2x' );
                    this.addTooltip( 'space_'+playable.x+'_'+playable.y, '', dojo.string.substitute( _("Play a tile less than or equal to ${m} here"), {m: playable.max} ) );
                }
            }
        },

        updatePlayerHand: function( playTile, drawTile )
        {
            // Remove the played tile from the hand
            if( playTile != null ){
                this.playerHand.removeFromStockById( playTile.id );
            }

            // Add the drawn tile to the hand
            if( drawTile != null ){
                this.playerHand.addToStockWithId(drawTile.type_arg, drawTile.id);
            }
        },

        /* @Override */
        updatePlayerOrdering() {
            this.inherited(arguments);
            dojo.place('tf7_game_summary', 'player_boards', 'first');
        },

        ///////////////////////////////////////////////////
        //// Player's action

        /*

            Here, you are defining methods to handle player's action (ex: results of mouse click on
            game objects).

            Most of the time, these methods:
            _ check the action is possible at this game state.
            _ make a call to the game server

        */

        /*
            Handle playing a tile (clicking a playable space).

            Since this will be called from an event handler, we need to pass
            along the game instance when registering the handler.
        */
        onPlayTile: function( event, game )
        {
            // Stop propagation and prevent any default handling of the event
            event.stopPropagation();
            event.preventDefault();

            if( event.target.classList.contains( 'tf7_playable' ) )
            {
                // Get the clicked space X and Y
                // Note: space id format is "space_X_Y"
                var coords = event.target.id.split('_');
                var x = coords[1];
                var y = coords[2];

                if( game.checkAction( 'playTile' ) )    // Check that this action is possible at this moment
                {
                    // Get the selected tiles (should only be 1)
                    var tiles = game.playerHand.getSelectedItems();

                    if( tiles.length == 1 )
                    {
                        // Get the tile played
                        var tile = tiles[0];

                        /*
                            Put the tile on the board, save the playables, 
                            clear the playables and remove the tile from the
                            player's hand.
                        */
                        var tPlayables = game.playables;
                        game.addPieceOnBoard( x, y, tile.type, game.player_id );
                        game.clearPlayables();
                        game.playerHand.removeFromStockById( tile.id );

                        /*
                            Add action buttons to confirm or undo the played tile
                            and start a countdown timer to auto-confirm the play 
                            if the player does nothing.
                        */
                        game.addActionButton("confirmTile_button", _("Confirm"), (function() {
                            /*
                                Remove the action buttons and submit the played tile action
                            */
                            game.removeActionButtons();
                            // Exactly 1 tile selected and confirmed, tell the server to process the played tile
                            game.ajaxcall( "/twentyfourseven/twentyfourseven/playTile.html", {
                                lock:true,
                                x:x,
                                y:y,
                                tileId:tile.id
                            }, game, function( result ) {} );
                        }));
                        game.addActionButton("undoTile_button", _("Undo tile played"), (function() {
                            /*
                                Remove the action buttons and return the tile to the 
                                player's hand.
                            */
                            game.removeActionButtons();
                            game.removePieceFromBoard( x, y, tile.type );
                            game.updatePlayables( tPlayables );
                            game.playerHand.addToStockWithId( tile.type, tile.id );
                        }), null, null, "gray");
                        game.startActionTimer("confirmTile_button", 5)
                    }
                    else
                    {
                        console.log('Wrong number of tiles selected - ' + tiles.length + '.');
                    }
                }
            }

        },

        ///////////////////////////////////////////////////
        //// Reaction to cometD notifications

        /*
            setupNotifications:

            In this method, you associate each of your game notifications with your local method to handle it.

            Note: game notification names correspond to "notifyAllPlayers" and "notifyPlayer" calls in
                  your twentyfourseven.game.php file.

        */
        setupNotifications: function()
        {
            dojo.subscribe( 'playTile', this, "notif_playTile" );
            this.notifqueue.setSynchronous( 'playTile', 500 );
            dojo.subscribe( 'newScores', this, "notif_newScores" );
            this.notifqueue.setSynchronous( 'newScores', 500 );
            dojo.subscribe( 'handChange', this, "notif_handChange" );
            this.notifqueue.setSynchronous( 'handChange', 500 );
            dojo.subscribe( 'cantPlay', this, "notif_cantPlay" );
            this.notifqueue.setSynchronous( 'cantPlay', 500 )
        },

        /*
         * Handle the play tile notification.
         */
        notif_playTile: function( notif )
        {
            // Clear the playables from the board
            this.clearPlayables();

            // Place new pieces on the board (played tile and any time out stones)
            for( const piece of notif.args.new_pieces )
            {
                this.addPieceOnBoard( piece.x, piece.y, piece.value, notif.args.player_id );
            }

            // Highlight any combos scored
            this.highlightCombos( notif.args );
        },

        /*
         * Handle the new scores notification.
         */
        notif_newScores: function( notif )
        {
            for( const player_id in notif.args.scores )
            {
                var newScore = notif.args.scores[ player_id ];
                var newTally = notif.args.tallies[ player_id ];
                var newHandSize = notif.args.hand_sizes[ player_id ];
                var newDeckSize = notif.args.deck_size;
                this.scoreCtrl[ player_id ].toValue( newScore );
                document.querySelector( '#tf7_game_summary .tf7_deck_size_value' ).textContent = newDeckSize;
                document.querySelector( '#tf7_player_board_'+player_id+' .tf7_hand_size_value' ).textContent = newHandSize;
            }
        },

        /*
         * Handle the hand change notification.
         */
        notif_handChange: function( notif )
        {
            this.updatePlayerHand( notif.args.playTile, notif.args.drawTile );
        },

        /*
         * Handle the cant play notification.
         */
        notif_cantPlay: function( notif )
        {
            console.log(notif.args);
        }

   });
});
