const selectSingle = document.querySelector('.warehouse__select');
const selectSingle_title = selectSingle.querySelector('.warehouse__select__title');
const selectSingle_labels = selectSingle.querySelectorAll('.warehouse__select__label');

let selectTitleFlag = document.querySelector(`.warehouse__select__title-flag`);

// Toggle menu
selectSingle_title.addEventListener('click', () => {
  if ('active' === selectSingle.getAttribute('data-state')) {
    selectSingle.setAttribute('data-state', '');
  } else {
    selectSingle.setAttribute('data-state', 'active');
  }
});

// Close when click to option
for (let i = 0; i < selectSingle_labels.length; i++) {
  selectSingle_labels[i].addEventListener('click', (evt) => {
    selectSingle_labels.forEach((item) => {
      item.classList.remove(`warehouse__select__label--active`);
    });
    evt.target.classList.add(`warehouse__select__label--active`);

    selectSingle_title.textContent = evt.target.textContent;
    selectSingle.setAttribute('data-state', '');

//    console.log(evt.target.textContent.indexOf(`Россия`));

    if ( -1 < evt.target.textContent.indexOf(`Россия`)) {
      selectTitleFlag.classList.add(`warehouse__select__title-flag--russia`);
      selectTitleFlag.classList.remove(`warehouse__select__title-flag--kazakhstan`);
    } else {
      selectTitleFlag.classList.remove(`warehouse__select__title-flag--russia`);
      selectTitleFlag.classList.add(`warehouse__select__title-flag--kazakhstan`);
    }
  });
}

