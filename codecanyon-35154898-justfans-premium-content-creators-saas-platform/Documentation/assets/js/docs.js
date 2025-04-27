"use strict";

var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
	return new bootstrap.Tooltip(tooltipTriggerEl)
})

/* ================================
 * Global Config Objects
 * ================================ */
const productConfig = {
	name: 'JustFans - Premium Content Creators SaaS platform',
	version: 'v8.0.0',
	lastUpdateDate: '3/15/2025'
};

// You might already define docsConfig in another file.
// Just ensure it’s accessible if you rely on docsConfig.initSidebar, etc.
const docsConfig = window.docsConfig || {
	initSidebar: true
};

/* ================================
 * Constants & DOM Elements
 * ================================ */
const OFFSET = 69; // Reuse for extra-scroll-nav
const sidebarToggler = document.getElementById('docs-sidebar-toggler');
const sidebar = document.getElementById('docs-sidebar');
const label = `Last updated: ${timeAgo(productConfig.lastUpdateDate)}`;

/* ================================
 * Responsive Sidebar
 * ================================ */
window.onload = function () {
	if (docsConfig.initSidebar) {
		responsiveSidebar();
	}
};

window.onresize = function () {
	if (docsConfig.initSidebar) {
		responsiveSidebar();
	}
};

function responsiveSidebar() {
	const w = window.innerWidth;
	if (w >= 1200) {
		sidebar.classList.remove('sidebar-hidden');
		sidebar.classList.add('sidebar-visible');
	} else {
		sidebar.classList.remove('sidebar-visible');
		sidebar.classList.add('sidebar-hidden');
	}
}

if (docsConfig.initSidebar) {
	sidebarToggler.addEventListener('click', () => {
		// Toggle sidebar classes
		if (sidebar.classList.contains('sidebar-visible')) {
			sidebar.classList.remove('sidebar-visible');
			sidebar.classList.add('sidebar-hidden');
		} else {
			sidebar.classList.remove('sidebar-hidden');
			sidebar.classList.add('sidebar-visible');
		}
	});
}

/* ================================
 * Unified Smooth Scrolling
 * - Sidebar links (.scrollto) use scrollIntoView
 * - Extra nav links (.extra-scroll-nav) use manual offset (69px)
 * ================================ */
const allNavLinks = document.querySelectorAll('#docs-sidebar .scrollto, .extra-scroll-nav');

allNavLinks.forEach((link) => {
	link.addEventListener('click', (e) => {
		// Prevent default jump
		e.preventDefault();

		// Identify target
		const targetId = link.getAttribute('href').replace('#', '');
		const targetElem = document.getElementById(targetId);

		// Safety check
		if (!targetElem) {
			console.warn(`No element found with id="${targetId}"`);
			return;
		}

		// Determine which scrolling approach
		if (link.classList.contains('extra-scroll-nav')) {
			// Manual offset
			const yPos = targetElem.getBoundingClientRect().top + window.pageYOffset - OFFSET;
			window.scrollTo({ top: yPos, behavior: 'smooth' });
		} else {
			// Normal smooth scroll
			targetElem.scrollIntoView({ behavior: 'smooth' });
		}

		// Update the URL hash
		history.pushState(null, null, `#${targetId}`);

		// Collapse sidebar if on mobile
		if (sidebar.classList.contains('sidebar-visible') && window.innerWidth < 1200) {
			sidebar.classList.remove('sidebar-visible');
			sidebar.classList.add('sidebar-hidden');
		}
	});
});

/* ================================
 * Gumshoe ScrollSpy
 * ================================ */
var spy = new Gumshoe('#docs-nav a', {
	offset: OFFSET // Sticky header height (69px)
});

/* ================================
 * SimpleLightbox Plugin
 * ================================ */
var lightbox = new SimpleLightbox('.simplelightbox-gallery a', {
	/* additional options if needed */
});

document.addEventListener('DOMContentLoaded', function () {
	const updateLabelElement = document.getElementById('last-updated-label');
	if (updateLabelElement) {
		updateLabelElement.textContent = label;
	}
});

// Function to calculate the human-readable time difference
function timeAgo(dateString) {
	const lastUpdate = new Date(dateString);
	const now = new Date();
	const diffTime = now - lastUpdate; // Difference in milliseconds

	const diffSeconds = Math.floor(diffTime / 1000);
	const diffMinutes = Math.floor(diffSeconds / 60);
	const diffHours = Math.floor(diffMinutes / 60);
	const diffDays = Math.floor(diffHours / 24);
	const diffMonths = Math.floor(diffDays / 30);
	const diffYears = Math.floor(diffMonths / 12);

	if (diffYears > 0) {
		return diffYears === 1 ? '1 year ago' : `${diffYears} years ago`;
	} else if (diffMonths > 0) {
		return diffMonths === 1 ? '1 month ago' : `${diffMonths} months ago`;
	} else if (diffDays > 0) {
		return diffDays === 1 ? '1 day ago' : `${diffDays} days ago`;
	} else if (diffHours > 0) {
		return diffHours === 1 ? '1 hour ago' : `${diffHours} hours ago`;
	} else if (diffMinutes > 0) {
		return diffMinutes === 1 ? '1 minute ago' : `${diffMinutes} minutes ago`;
	} else {
		return 'Just now';
	}
}
