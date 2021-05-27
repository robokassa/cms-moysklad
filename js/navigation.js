let warehouseNavigationItemNotification = document.querySelector(`.warehouse__navigation-item--notification`);
let warehouseNavigationItemApi = document.querySelector(`.warehouse__navigation-item--api`);
let warehouseNavigationItemPayment = document.querySelector(`.warehouse__navigation-item--payment`);

let warehouseNavigationItemList = document.querySelectorAll(`.warehouse__navigation-item`);
let warehouseNavigationContentList = document.querySelectorAll(`.warehouse__navigation-content`);

let warehouseNavigationContentNotification = document.querySelector(`.warehouse__navigation-content--notification`);
let warehouseNavigationContentApi = document.querySelector(`.warehouse__navigation-content--api`);
let warehouseNavigationContentPayment = document.querySelector(`.warehouse__navigation-content--payment`);

let warehouseInfoText = document.querySelector(`.warehouse__info-text`);
let warehouseSupport = document.querySelector(`.warehouse__support`);
let overlay = document.querySelector(`.overlay`);
let warehouseCross = document.querySelector(`.warehouse__cross`);

const onNavigationItemNotificationClick = () => {
  warehouseNavigationItemList.forEach((item) => {
    item.classList.remove(`warehouse__navigation-item--active`);
  });

  warehouseNavigationContentList.forEach((item) => {
    item.classList.remove(`warehouse__navigation-content--show`);
  });

  warehouseNavigationItemNotification.classList.add(`warehouse__navigation-item--active`);
  warehouseNavigationContentNotification.classList.add(`warehouse__navigation-content--show`);
}

const onNavigationItemApiClick = () => {
  warehouseNavigationItemList.forEach((item) => {
    item.classList.remove(`warehouse__navigation-item--active`);
  });

  warehouseNavigationContentList.forEach((item) => {
    item.classList.remove(`warehouse__navigation-content--show`);
  });

  warehouseNavigationItemApi.classList.add(`warehouse__navigation-item--active`);
  warehouseNavigationContentApi.classList.add(`warehouse__navigation-content--show`);
}

const onNavigationItemPaymentClick = () => {
  warehouseNavigationItemList.forEach((item) => {
    item.classList.remove(`warehouse__navigation-item--active`);
  });

  warehouseNavigationContentList.forEach((item) => {
    item.classList.remove(`warehouse__navigation-content--show`);
  });

  warehouseNavigationItemPayment.classList.add(`warehouse__navigation-item--active`);
  warehouseNavigationContentPayment.classList.add(`warehouse__navigation-content--show`);
}

const onWarehouseInfoTextClick = () => {
  warehouseSupport.classList.remove(`warehouse__support--hidden`);
  overlay.classList.remove(`overlay--invis`); 
}

const onWarehouseCrossClick = () => {
  warehouseSupport.classList.add(`warehouse__support--hidden`);
  overlay.classList.add(`overlay--invis`); 
}

const onOverlayClick = () => {
  warehouseSupport.classList.add(`warehouse__support--hidden`);
  overlay.classList.add(`overlay--invis`); 
}

warehouseNavigationItemNotification.addEventListener(`click`, onNavigationItemNotificationClick);
warehouseNavigationItemApi.addEventListener(`click`, onNavigationItemApiClick);
warehouseNavigationItemPayment.addEventListener(`click`, onNavigationItemPaymentClick);

warehouseInfoText.addEventListener(`click`, onWarehouseInfoTextClick);
warehouseCross.addEventListener(`click`, onWarehouseCrossClick);
overlay.addEventListener(`click`, onOverlayClick);