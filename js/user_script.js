let profile = document.querySelector('.header .flex .profile-detail');

document.querySelector('#user-btn').onclick = () =>{
	profile.classList.toggle('active');
	console.log("Profile Active:", profile.classList.contains('active'));
	searchForm.classList.remove('active');
}

let searchForm = document.querySelector('.header .flex .search-form');
document.querySelector('#search-btn').onclick = () => {
	searchForm.classList.toggle('active');
	profile.classList.remove('active')
}

let navbar = document.querySelector('.navbar');

document.querySelector('#menu-btn').onclick = () => {
	navbar.classList.toggle('active');
}

/*-----------------home slider -----------------*/

const imgBox = document.querySelector('.slider-container');
const slides = document.getElementsByClassName('slideBox');
var i = 0;

function nextSlide() {
	slides[i].classList.remove('active');
	i = (i + 1) % slides.length;
	slides[i].classList.add('active');
}

function prevSlide() {
	slides[i].classList.remove('active');
	i = (i - 1 + slides.length) % slides.length;
	slides[i].classList.add('active');
}