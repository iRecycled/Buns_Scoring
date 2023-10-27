export default class Scoring {

    constructor(){

    }

    init(){
    function changeTab(){
        console.log("hello");
        }

    document.getElementById('qualifying').addEventListener('click', function() {
      console.log("clicked");
    });

    // Get all the list items
    const listItems = document.querySelectorAll('.tab-link');

    // Get the content container
    const contentContainer = document.getElementById('content');

    // Add click event listeners to each list item
    listItems.forEach(item => {
        item.addEventListener('click', () => {
            // Get the value of the 'data-tab' attribute
            const tab = item.getAttribute('data-tab');
            console.log(tab);

            // Update the content based on the clicked tab
            if (tab == 'qualifying') {
                contentContainer.textContent = 'Qualifying content here.';
            } else if (tab == 'heats') {
                contentContainer.textContent = 'Heats content here.';
            } else if (tab == 'consolation') {
                contentContainer.textContent = 'Consolation content here.';
            } else if (tab == 'feature') {
                contentContainer.textContent = 'Feature content here.';
            }

            // Remove the 'active' class from all items and add it to the clicked item
            listItems.forEach(item => {
                item.classList.remove('active');
            });
            item.classList.add('active');
        });
    });
    }
}
    // Create an instance of the class and initialize it
    const scoringInstance = new Scoring();
    scoringInstance.init();
