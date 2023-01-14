/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * TwentyFourSeven implementation : © Jim Ginn ginn5j@gmail.com
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
            console.log('twentyfourseven constructor');

            // Here, you can init the global variables of your user interface
            // Example:
            // this.myGlobalValue = 0;
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
            console.log( "Starting game setup" );

            // Setting up player boards
            for( var player_id in gamedatas.players )
            {
                var player = gamedatas.players[player_id];

                // TODO: Setting up players boards if needed
            }

            // Player hand
            this.playerHand = new ebg.stock();
            this.playerHand.create(this, $('tf7_player_hand'), this.tilewidth, this.tileheight);
            this.playerHand.image_items_per_row = 5;
            this.playerHand.centerItems = true;
            this.playerHand.extraClasses = 'playerTile';
            this.playerHand.setSelectionMode( 1 );
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

            console.log( "Ending game setup" );
        },


        ///////////////////////////////////////////////////
        //// Game & client states

        // onEnteringState: this method is called each time we are entering into a new game state.
        //                  You can use this method to perform some user interface changes at this moment.
        //
        onEnteringState: function( stateName, args )
        {
            console.log( 'Entering state: '+stateName );

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
            console.log( 'Leaving state: '+stateName );

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

        // onUpdateActionButtons: in this method you can manage "action buttons" that are displayed in the
        //                        action status bar (ie: the HTML links in the status bar).
        //
        onUpdateActionButtons: function( stateName, args )
        {
            console.log( 'onUpdateActionButtons: '+stateName );

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
                        "description" : combo.description,
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
                dojo.addClass(r, "tf7_combo_anim");
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
                    this.addTooltip( 'space_'+playable.x+'_'+playable.y, '', _('Play a tile less than or equal to '+playable.max+' here.') );
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

                        // Put it on the board
                        game.addPieceOnBoard( x, y, tile.type, game.player_id );
                        // TODO: REMOVE TILE FROM PLAYER'S HAND

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
                            // TODO: ADD TILE BACK TO PLAYER'S HAND
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
            console.log( 'notifications subscriptions setup' );

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
                /*
                    Only add the piece if it is a stone (0) or when the current 
                    player is not the player that played the tile (because we've 
                    already placed the tile for the player that played the tile).
                */
                if( piece.value <= 0 || this.player_id != notif.args.player_id )
                {
                    this.addPieceOnBoard( piece.x, piece.y, piece.value, notif.args.player_id );
                }
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
                this.scoreCtrl[ player_id ].toValue( newScore );
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
