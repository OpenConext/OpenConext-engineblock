/**
 * Manages navigation using arrow and return key
 * This singleton shouldn't be used for any other project (it's kinda messy)
 *
 * @author Ward
 */
var keyboardNavigator = {
    
    MODE_3COLUMN_GRID: 0, //Const
    MODE_LIST: 1, //Const
    
    enabled:true,
    
    preventMouseEvents: false, //Indicates if mouseenter events should be handled
    
    selectedIndex: -1,
    
    /* Increments to use for each arrow key */
    leftIncrement: -1,
    upIncrement: -3,
    rightIncrement: 1,
    downIncrement: 3,
    
    itemSelector:'#organisationsContainer li',
    selectedClass: 'selected',
    allItems:{},
    
    /**
     * Hooks up the keypress event
     */
    init: function() {
        $(document).keydown(function(e) {
            //We prevent selection with the mouse as long as keys are down, this is to prevent flickering and jumping selections
            keyboardNavigator.preventMouseEvents = true;
            if(keyboardNavigator.enabled == true) {
                switch(e.which) {
                    case 13: //Return
                        keyboardNavigator.activate();
                        break;
                    
                    case 37: //Left
                        keyboardNavigator.navigate('left');
                        break;
                    
                    case 38: //Up
                        keyboardNavigator.navigate('up');
                        break;
                        
                    case 39: //Right
                        keyboardNavigator.navigate('right');
                        break;
                        
                    case 40: //Down
                        keyboardNavigator.navigate('down');
                        break;
                }
            }
        });
        
        $(document).keyup(function(e) {
            //Allow mouse selected when no keys are down
            keyboardNavigator.preventMouseEvents = false;
        });
        
        //Update selectedClass on mouse movement
        $(this.itemSelector).live('mouseenter', function() {
            //Handle mouse events only if they aren't prevented
            if(keyboardNavigator.preventMouseEvents == false) {
                keyboardNavigator.setSelectedIndex($(this).index() );
            }
        });
    },
    
    
    determineSelectedIndex: function() {
        //Determine selected index based on selectedClass
        this.selectedIndex = $(this.itemSelector + '.' + this.selectedClass).index();
    },
    
    
    /**
     * Updates the selectedIndex and calls updateSelectionHTML when done
     */
    navigate: function(direction) {
        //If there is no selection, we go to the first any in any case
        
        if(this.selectedIndex == -1) {
            this.selectedIndex = 0;
        } else {
            
            //Check the direction and increment or decrement the selectedIndex
            if(direction == 'left') {
                this.selectedIndex = this.selectedIndex + this.leftIncrement;
            
            } else if(direction == 'right') {
                this.selectedIndex = this.selectedIndex + this.rightIncrement;
            
            } else if(direction == 'up') {
                this.selectedIndex = this.selectedIndex + this.upIncrement;
            
            } else if (direction == 'down') {
                this.selectedIndex = this.selectedIndex + this.downIncrement;
            }
            
            //Make sure the selectedIndex is not out of bounds
            if(this.selectedIndex < 0 ) {
                this.selectedIndex = 0;
            } else if(this.selectedIndex > $(this.itemSelector).size() - 1 ) {
                this.selectedIndex = $(this.itemSelector).size() - 1;
            }
        }
        
        this.updateSelectionHTML();
    },
    
    
    /**
     * Updates the html to reflect the selectedIndex
     */
    updateSelectionHTML: function() {
        $(this.itemSelector).removeClass(this.selectedClass);
        
        var selectedItem = $(this.itemSelector).eq(this.selectedIndex);
        selectedItem.addClass(this.selectedClass);
        selectedItem.focus();
    },
    
    /**
     * Calls the onclick handler on the selected element
     */
    activate: function() {
        $(this.itemSelector).eq(this.selectedIndex).click();
    },
    
    /**
     * Set selected index
     * @param    index    new selected index
     */
    setSelectedIndex: function(index) {
        this.selectedIndex = index;
        this.updateSelectionHTML();
    },
    
    
    /**
     * Switches between 3 column grid mode and list mode
     * @param    mode    mode to switch to, use one of the 'constants' (MODE_3COLUMN_GRID | MODE_LIST)
     */
    setMode: function(mode) {
        if(mode == this.MODE_3COLUMN_GRID) {
            this.leftIncrement = -1;
            this.upIncrement = -3;
            this.rightIncrement = 1;
            this.downIncrement = 3;
            
        } else if(mode == this.MODE_LIST) {
            this.leftIncrement = 0;
            this.upIncrement = -1;
            this.rightIncrement = 0;
            this.downIncrement = 1;
        }
    
        this.setSelectedIndex(0);
    }
};