const selectSingleTax = document.querySelector('.warehouse__select-tax');
const selectSingle_titleTax = selectSingleTax.querySelector('.warehouse__select__title-tax');
const selectSingle_labelsTax = selectSingleTax.querySelectorAll('.warehouse__select__label-tax');

// Toggle menu
selectSingle_titleTax.addEventListener('click', () => {
  if ('active' === selectSingleTax.getAttribute('data-state')) {
    selectSingleTax.setAttribute('data-state', '');
  } else {
    selectSingleTax.setAttribute('data-state', 'active');
  }
});

// Close when click to option
for (let i = 0; i < selectSingle_labelsTax.length; i++) {
  selectSingle_labelsTax[i].addEventListener('click', (evt) => {
    selectSingle_labelsTax.forEach((item) => {
      item.classList.remove(`warehouse__select__label--active`);
    });
    evt.target.classList.add(`warehouse__select__label--active`);
    selectSingle_titleTax.textContent = evt.target.textContent;
    selectSingleTax.setAttribute('data-state', '');
  });
}

