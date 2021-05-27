const selectSingleSub = document.querySelector('.warehouse__select-sub');
const selectSingle_titleSub = selectSingleSub.querySelector('.warehouse__select__title-sub');
const selectSingle_labelsSub = selectSingleSub.querySelectorAll('.warehouse__select__label-sub');

// Toggle menu
selectSingle_titleSub.addEventListener('click', () => {
  if ('active' === selectSingleSub.getAttribute('data-state')) {
    selectSingleSub.setAttribute('data-state', '');
  } else {
    selectSingleSub.setAttribute('data-state', 'active');
  }
});

// Close when click to option
for (let i = 0; i < selectSingle_labelsSub.length; i++) {
  selectSingle_labelsSub[i].addEventListener('click', (evt) => {
    selectSingle_labelsSub.forEach((item) => {
      item.classList.remove(`warehouse__select__label--active`);
    });
    evt.target.classList.add(`warehouse__select__label--active`);
    selectSingle_titleSub.textContent = evt.target.textContent;
    selectSingleSub.setAttribute('data-state', '');
  });
}