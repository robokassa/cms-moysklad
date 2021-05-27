let warehouseInputRadioList = document.querySelectorAll(`.warehouse__input-radio`);
let warehouseLabelList = document.querySelectorAll(`.warehouse__label`);

let warehouseNavigationApi = document.querySelector(`.warehouse__navigation-content--api`);
let warehouseNavigationPayment = document.querySelector(`.warehouse__navigation-content--payment`);

let fieldTestApiList = warehouseNavigationApi.querySelectorAll(`.warehouse__field--test`);
let fieldTestPaymentList = warehouseNavigationPayment.querySelectorAll(`.warehouse__field--fiscalization`);

let InputRadioApiList = warehouseNavigationApi.querySelectorAll(`.warehouse__input-radio`);
let InputRadioPaymentList = warehouseNavigationPayment.querySelectorAll(`.warehouse__input-radio`);

const inputApiToggle = (evt) => {
  let value = evt.target.value;
  
  if (value === `off`) {
    fieldTestApiList.forEach((item) => {
      item.classList.add(`warehouse__field--hidden`);
    });
  } else {
    fieldTestApiList.forEach((item) => {
      item.classList.remove(`warehouse__field--hidden`);
    });
  }
}

const inputPaymentToggle = (evt) => {
  let value = evt.target.value;

  if (value === `off`) {
    fieldTestPaymentList.forEach((item) => {
      item.classList.add(`warehouse__field--hidden`);
    });
  } else {
    fieldTestPaymentList.forEach((item) => {
      item.classList.remove(`warehouse__field--hidden`);
    });
  }
}

InputRadioApiList.forEach((item) => {
  item.addEventListener(`click`, inputApiToggle);
});

InputRadioPaymentList.forEach((item) => {
  item.addEventListener(`click`, inputPaymentToggle);
});

