import {initializePage} from './page';
import {handlePreviousSelectionVisible} from './wayf/handlePreviousSelectionVisible';
import {keyboardBehaviour} from './wayf/keyboardBehaviour';
import {mouseBehaviour} from './wayf/mouseBehaviour';

export function initializeWayf() {
  const callbacksAfterLoad = () => {
    // Initialize variables
    const selectedIdps = document.querySelector('.wayf__previousSelection');
    const configuration = JSON.parse(document.getElementById('wayf-configuration').innerHTML);
    const previouslySelectedIdps = configuration.previousSelectionList;

    // Initialize behaviour
    handlePreviousSelectionVisible(selectedIdps, previouslySelectedIdps);
    keyboardBehaviour(previouslySelectedIdps);
    mouseBehaviour(previouslySelectedIdps);
  };

  initializePage('main.wayf', callbacksAfterLoad);
}
