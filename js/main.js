/* start navbar */

let saved_btn = $('.navbar .saved button');
let account_btn = $('.navbar .account button');$ 

$('.navbar-light .navbar-toggler').click(() => {
    saved_btn.toggleClass("disabled");
    account_btn.toggleClass("disabled");
})

/* end navbar */

function filterContent(type) {
    const cards = document.querySelectorAll('.card');
    cards.forEach(card => {
        const cardType = card.querySelector('.card-text').textContent.trim();
        if (type === '' || cardType === type) {
            card.parentElement.style.display = 'block';
        } else {
            card.parentElement.style.display = 'none';
        }
    });
}