const selectSingleRate = document.querySelector('.warehouse__select-rate');
const selectSingle_titleRate = selectSingleRate.querySelector('.warehouse__select__title-rate');
const selectSingle_labelsRate = selectSingleRate.querySelectorAll('.warehouse__select__label-rate');

// Toggle menu
selectSingle_titleRate.addEventListener('click', () => {
  if ('active' === selectSingleRate.getAttribute('data-state')) {
    selectSingleRate.setAttribute('data-state', '');
  } else {
    selectSingleRate.setAttribute('data-state', 'active');
  }
});

// Close when click to option
for (let i = 0; i < selectSingle_labelsRate.length; i++) {
  selectSingle_labelsRate[i].addEventListener('click', (evt) => {
    selectSingle_labelsRate.forEach((item) => {
      item.classList.remove(`warehouse__select__label--active`);
    });
    evt.target.classList.add(`warehouse__select__label--active`);
    selectSingle_titleRate.textContent = evt.target.textContent;
    selectSingleRate.setAttribute('data-state', '');
  });
}