const selectSingleMethod = document.querySelector('.warehouse__select-method');
const selectSingle_titleMethod = selectSingleMethod.querySelector('.warehouse__select__title-method');
const selectSingle_labelsMethod = selectSingleMethod.querySelectorAll('.warehouse__select__label-method');

// Toggle menu
selectSingle_titleMethod.addEventListener('click', () => {
  if ('active' === selectSingleMethod.getAttribute('data-state')) {
    selectSingleMethod.setAttribute('data-state', '');
  } else {
    selectSingleMethod.setAttribute('data-state', 'active');
  }
});

// Close when click to option
for (let i = 0; i < selectSingle_labelsMethod.length; i++) {
  selectSingle_labelsMethod[i].addEventListener('click', (evt) => {
    selectSingle_labelsMethod.forEach((item) => {
      item.classList.remove(`warehouse__select__label--active`);
    });
    evt.target.classList.add(`warehouse__select__label--active`);
    selectSingle_titleMethod.textContent = evt.target.textContent;
    selectSingleMethod.setAttribute('data-state', '');
  });
}