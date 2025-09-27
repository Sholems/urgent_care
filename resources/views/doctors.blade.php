@extends('layouts.app')

@section('title', 'Lizelsundria Urgent Care - Doctors')

@section('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection

@section('content')
    <div class="breadcrumb-area bg-gradient text-center">
        <div class="fixed-bg" style="background-image: url({{ asset('img/shape/9.png') }});"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-8 offset-lg-2">
                    <h1>Find Your Doctor</h1>
                    <ul class="breadcrumb">
                        <li><a href="#"><i class="fas fa-home"></i> Home</a></li>
                        <li class="active">Doctor</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="team-default-area bg-gray default-padding bottom-less">
        <div class="container">
            <div class="row mb-4">
                <div class="col-12">
                    <div class="search-filter-card">
                        <div class="row g-3 align-items-center">
                            <div class="col-lg-4 col-md-5">
                                <div class="search-input-wrapper">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" class="form-control search-input" id="doctorSearch"
                                        placeholder="Search by doctor name or specialty...">
                                </div>
                            </div>

                            <div class="col-lg-3 col-md-3 mb-2" style="margin-top: 21px;">
                                <div class="sort-wrapper">
                                    <select class="form-select sort-select" id="sortSelect">
                                        <option value="">Sort By</option>
                                        <option value="price_low_high"
                                            {{ request('sort_by') == 'price_low_high' ? 'selected' : '' }}>
                                            Price: Low to High</option>
                                        <option value="price_high_low"
                                            {{ request('sort_by') == 'price_high_low' ? 'selected' : '' }}>
                                            Price: High to Low</option>
                                        <option value="rating_high_low"
                                            {{ request('sort_by') == 'rating_high_low' ? 'selected' : '' }}>
                                            Rating: High to Low</option>
                                        <option value="experience_high_low"
                                            {{ request('sort_by') == 'experience_high_low' ? 'selected' : '' }}>
                                            Experience: High to Low</option>
                                    </select>
                                </div>
                            </div>

                            <button type="button" class="btn btn-primary" id="applyFilters">
                                Apply
                            </button>


                            <div class="col-lg-2 col-md-2">
                                <button class="btn btn-filter w-100" data-bs-toggle="modal" data-bs-target="#filterModal">
                                    <i class="fas fa-sliders-h me-1"></i>
                                    <span class="d-none d-md-inline">Filter</span>
                                    <span class="filter-badge" id="filterCount" style="display: none;"></span>
                                </button>
                            </div>

                            <div class="col-lg-2 col-md-2">
                                <div class="results-info">
                                    <span class="results-count" id="resultsCount">{{ count($doctors) }}</span>
                                    <span class="results-text">doctors</span>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3" id="activeFilters" style="display: none;">
                            <div class="col-12">
                                <div class="active-filters-wrapper">
                                    <span class="active-filters-label">Active Filters:</span>
                                    <div id="filterTags" class="filter-tags-container"></div>
                                    <button class="btn btn-clear-filters" id="clearAllFilters">
                                        <i class="fas fa-times me-1"></i>Clear All
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Cards -->
            <div class="team-items text-center">
                <div class="row g-4" id="doctorsContainer">
                    @include('partials.doctor_cards', ['doctors' => $doctors])
                </div>
            </div>
        </div>
    </div>


    <!-- Enhanced Filter Modal -->
    <div class="modal fade" id="filterModal" tabindex="-1" aria-labelledby="filterModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content filter-modal">
                <div class="modal-header">
                    <h5 class="modal-title text-white" id="filterModalLabel">
                        <i class="fas fa-sliders-h me-2"></i>Filter Doctors
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="filterForm">
                        <div class="row g-4">
                            <div class="col-md-6">

                                <div class="filter-group">
                                    <label for="ratingFilter" class="filter-label">
                                        <i class="fas fa-star text-warning me-2" style="margin-right: 8px;"></i>Minimum
                                        Rating
                                    </label>
                                    <select class="form-select" id="ratingFilter" name="rating">
                                        <option value="">Any Rating</option>
                                        <option value="5">⭐⭐⭐⭐⭐ 5 Stars</option>
                                        <option value="4">⭐⭐⭐⭐ 4+ Stars</option>
                                        <option value="3">⭐⭐⭐ 3+ Stars</option>
                                        <option value="2">⭐⭐ 2+ Stars</option>
                                        <option value="1">⭐ 1+ Stars</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="filter-group">
                                    <label for="genderFilter" class="filter-label">
                                        <i class="fas fa-users me-2" style="margin-right: 8px;"></i>Gender Preference
                                    </label>
                                    <select class="form-select" id="genderFilter" name="gender">
                                        <option value="">Any Gender</option>
                                        <option value="male">Male Doctor</option>
                                        <option value="female">Female Doctor</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Price Range -->
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-dollar-sign text-success" style="margin-right: 8px;"></i>Price Range
                            </label>
                            <div class="price-range-wrapper">
                                <div class="price-input-group">
                                    <input type="number" class="form-control" id="minPrice" name="minPrice"
                                        placeholder="$0" min="0">
                                </div>
                                <div class="price-separator">-</div>
                                <div class="price-input-group">
                                    <input type="number" class="form-control" id="maxPrice" name="maxPrice"
                                        placeholder="$500" min="0">
                                </div>
                            </div>
                        </div>

                        <!-- Availability Time Slots -->
                        <div class="filter-group">
                            <label class="filter-label">
                                <i class="fas fa-clock text-info me-2"></i>Available Time Slots
                            </label>
                            <div class="time-slots-grid">
                                <div class="time-slot-item">
                                    <input class="form-check-input" type="checkbox" id="time1" name="timeSlots[]"
                                        value="9am-12pm">
                                    <label class="form-check-label time-slot-label" for="time1">
                                        9:00 AM - 12:00 PM
                                    </label>
                                </div>
                                <div class="time-slot-item">
                                    <input class="form-check-input" type="checkbox" id="time2" name="timeSlots[]"
                                        value="12pm-3pm">
                                    <label class="form-check-label time-slot-label" for="time2">
                                        12:00 PM - 3:00 PM
                                    </label>
                                </div>
                                <div class="time-slot-item">
                                    <input class="form-check-input" type="checkbox" id="time3" name="timeSlots[]"
                                        value="3pm-6pm">
                                    <label class="form-check-label time-slot-label" for="time3">
                                        3:00 PM - 6:00 PM
                                    </label>
                                </div>
                                <div class="time-slot-item">
                                    <input class="form-check-input" type="checkbox" id="time4" name="timeSlots[]"
                                        value="6pm-9pm">
                                    <label class="form-check-label time-slot-label" for="time4">
                                        6:00 PM - 9:00 PM
                                    </label>
                                </div>
                                <div class="time-slot-item">
                                    <input class="form-check-input" type="checkbox" id="time5" name="timeSlots[]"
                                        value="9pm-12am">
                                    <label class="form-check-label time-slot-label" for="time5">
                                        9:00 PM - 12:00 AM
                                    </label>
                                </div>
                            </div>
                        </div>


                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" id="resetFilters">
                        <i class="fas fa-undo me-1"></i>Reset All
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="applyFilters">
                        <i class="fas fa-check me-1"></i>Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doctorsContainer = document.getElementById('doctorsContainer');
            const searchInput = document.getElementById('doctorSearch');
            const sortSelect = document.getElementById('sortSelect');
            const resultsCount = document.getElementById('resultsCount');
            const filterCount = document.getElementById('filterCount');
            const activeFiltersDiv = document.getElementById('activeFilters');
            const filterTags = document.getElementById('filterTags');
            const noResults = document.getElementById('noResults');

            let currentFilters = {}; 
            let isLoading = false;
            let filterModal = null;

            // Initialize Bootstrap modal
            const filterModalElement = document.getElementById('filterModal');
            if (filterModalElement) {
                filterModal = new bootstrap.Modal(filterModalElement);
            }

            function closeModal() {
                if (filterModal) filterModal.hide();
            }

            // Function to parse URL parameters and populate initial filters
            function initializeFiltersFromUrl() {
                const urlParams = new URLSearchParams(window.location.search);

                // Reset currentFilters to ensure a clean slate
                currentFilters = {};

                if (urlParams.get('rating')) {
                    currentFilters.rating = urlParams.get('rating');
                    document.getElementById('ratingFilter').value = urlParams.get('rating');
                }
                if (urlParams.get('gender')) {
                    currentFilters.gender = urlParams.get('gender');
                    document.getElementById('genderFilter').value = urlParams.get('gender');
                }
                if (urlParams.get('minPrice')) {
                    currentFilters.minPrice = urlParams.get('minPrice');
                    document.getElementById('minPrice').value = urlParams.get('minPrice');
                }
                if (urlParams.get('maxPrice')) {
                    currentFilters.maxPrice = urlParams.get('maxPrice');
                    document.getElementById('maxPrice').value = urlParams.get('maxPrice');
                }
                const timeSlotsFromUrl = urlParams.getAll('timeSlots[]');
                if (timeSlotsFromUrl.length > 0) {
                    currentFilters.timeSlots = timeSlotsFromUrl;
                    timeSlotsFromUrl.forEach(slot => {
                        const checkbox = document.querySelector(
                            `input[name="timeSlots[]"][value="${slot}"]`);
                        if (checkbox) checkbox.checked = true;
                    });
                }
                // Set search input if present in URL
                if (urlParams.get('search')) {
                    searchInput.value = urlParams.get('search');
                }
                // Set sort select if present in URL
                if (urlParams.get('sort_by')) {
                    sortSelect.value = urlParams.get('sort_by');
                }
            }


            function loadDoctors(sortBy = '', lat = null, lng = null, filters = {}) {
                if (isLoading) return;
                isLoading = true;

                doctorsContainer.innerHTML = `
            <div class="col-12 text-center my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden"></span>
                </div>
            </div>`;

                const data = new URLSearchParams();
                const searchTerm = searchInput.value.trim();
                if (searchTerm) data.append('search', searchTerm);
                if (sortBy) data.append('sort_by', sortBy);
                if (lat && lng) {
                    data.append('lat', lat);
                    data.append('lng', lng);
                }
                Object.keys(filters).forEach(key => {
                    const value = filters[key];
                    if (Array.isArray(value)) value.forEach(v => data.append(`${key}[]`, v));
                    else if (value !== null && value !== '') data.append(key, value);
                });

                const url = `${window.location.pathname}?${data.toString()}`;

                fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin'
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.html !== undefined) {
                            doctorsContainer.innerHTML = data.html;
                            if (resultsCount) resultsCount.textContent = data.count;
                            if (noResults) noResults.style.display = data.count === 0 ? 'block' : 'none';
                            window.history.replaceState({}, '', url);
                        } else {
                            throw new Error('Invalid response format or missing HTML data.');
                        }
                    })
                    .catch(err => {
                        console.error('Fetch error:', err);
                        doctorsContainer.innerHTML = `
                    <div class="col-12 text-center text-danger">
                        <p>Error loading doctors.</p>
                        <p class="small">${err.message}</p>
                    </div>`;
                    })
                    .finally(() => {
                        isLoading = false;
                        updateActiveFilters();
                    });
            }

            // ===== SEARCH =====
            let searchTimeout;
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    const sortBy = sortSelect.value;
                    // Always include currentFilters in the load, even for search
                    if (sortBy === 'distance') handleDistanceSort();
                    else loadDoctors(sortBy, null, null, currentFilters);
                }, 300);
            });

            // ===== SORT =====
            sortSelect.addEventListener('change', function() {
                const sortBy = sortSelect.value;
                if (sortBy === 'distance') {
                    handleDistanceSort();
                } else {

                    loadDoctors(sortBy, null, null, currentFilters);
                }
                // updateActiveFilters() will be called in loadDoctors.finally()
            });


            // ===== DISTANCE SORT =====
            function handleDistanceSort() {
                if (navigator.geolocation) {
                    // Show a temporary message to the user that we are requesting location
                    doctorsContainer.innerHTML = `
                <div class="col-12 text-center my-4">
                    <p><i class="fas fa-map-marker-alt me-2"></i>Requesting your location...</p>
                    <div class="spinner-border text-info" role="status">
                        <span class="visually-hidden"></span>
                    </div>
                </div>`;

                    navigator.geolocation.getCurrentPosition(
                        position => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            loadDoctors('distance', lat, lng, currentFilters);
                        },
                        error => {
                            let errorMessage = 'Unable to get your location.';

                            switch (error.code) {
                                case error.PERMISSION_DENIED:
                                    errorMessage =
                                        'Location access denied by user. Please enable location permissions in your browser settings to use "Nearest to Me" sorting.';
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    errorMessage = 'Location information is unavailable.';
                                    break;
                                case error.TIMEOUT:
                                    errorMessage = 'The request to get user location timed out.';
                                    break;
                                default:
                                    errorMessage =
                                        'An unknown error occurred while trying to get your location.';
                                    break;
                            }
                            alert(errorMessage);
                            console.error('Geolocation Error:', error);
                            sortSelect.value = '';
                            loadDoctors('', null, null, currentFilters);
                        }, {
                            enableHighAccuracy: true,
                            timeout: 10000,
                            maximumAge: 0
                        }
                    );
                } else {
                    alert(
                        'Geolocation is not supported by your browser. Please update your browser or use a different one to use "Nearest to Me" sorting.'
                    );
                    sortSelect.value = '';
                    loadDoctors('', null, null, currentFilters);
                }
            }

            // ===== APPLY FILTERS =====
            document.getElementById('applyFilters').addEventListener('click', function() {
                const formData = new FormData(document.getElementById('filterForm'));
                // Re-construct currentFilters completely from the form to ensure it's up-to-date
                currentFilters = {};
                if (formData.get('rating')) currentFilters.rating = formData.get('rating');
                if (formData.get('gender')) currentFilters.gender = formData.get('gender');
                if (formData.get('minPrice')) currentFilters.minPrice = formData.get('minPrice');
                if (formData.get('maxPrice')) currentFilters.maxPrice = formData.get('maxPrice');
                const timeSlots = formData.getAll('timeSlots[]');
                if (timeSlots.length > 0) currentFilters.timeSlots = timeSlots;

                closeModal();
                const sortBy = sortSelect.value;
                if (sortBy === 'distance') handleDistanceSort();
                else loadDoctors(sortBy, null, null, currentFilters);
                // updateActiveFilters() will be called in loadDoctors.finally()
            });

            // ===== RESET FILTERS =====
            document.getElementById('resetFilters').addEventListener('click', function() {
                document.getElementById('filterForm').reset();
                currentFilters = {}; // Clear current filters
                loadDoctors(sortSelect.value, null, null, currentFilters);
                // updateActiveFilters() will be called in loadDoctors.finally()
            });

            // ===== CLEAR ALL =====
            document.getElementById('clearAllFilters').addEventListener('click', function() {
                document.getElementById('filterForm').reset();
                searchInput.value = '';
                sortSelect.value = ''; // Reset sort dropdown
                currentFilters = {}; // Clear all filters
                loadDoctors('', null, null,
                    currentFilters); // Load doctors with no search, sort, or filters
                // updateActiveFilters() will be called in loadDoctors.finally()
            });

            // ===== ACTIVE FILTERS DISPLAY =====
            function updateActiveFilters() {
                const filterKeys = Object.keys(currentFilters);
                const hasSearch = searchInput.value.trim() !== '';
                const hasSort = sortSelect.value !== '';
                const hasActiveFilters = filterKeys.length > 0 || hasSearch || hasSort;

                if (hasActiveFilters) {
                    let count = filterKeys.length;
                    if (hasSearch) count++;
                    if (hasSort && sortSelect.value !== '') count++; // Only count if a specific sort is selected

                    filterCount.textContent = count;
                    activeFiltersDiv.style.display = 'block';
                    filterTags.innerHTML = '';

                    if (hasSearch) addFilterTag('Search', `"${searchInput.value}"`, 'search');
                    if (hasSort && sortSelect.value !== '') addFilterTag('Sort', sortSelect.options[sortSelect
                        .selectedIndex].text, 'sort');

                    filterKeys.forEach(key => {
                        let value = currentFilters[key];
                        let label = key.charAt(0).toUpperCase() + key.slice(1).replace(/([A-Z])/g, ' $1');
                        if (key === 'timeSlots') value = `${value.length} slot(s)`;
                        else if (key === 'rating') value = `${value}+ stars`;
                        else if (key === 'minPrice') {
                            label = 'Min Price';
                            value = `$${value}`;
                        } else if (key === 'maxPrice') {
                            label = 'Max Price';
                            value = `$${value}`;
                        }
                        addFilterTag(label, value, key);
                    });
                } else {
                    filterCount.style.display = 'none';
                    activeFiltersDiv.style.display = 'none';
                }
            }

            // Added a 'type' parameter to addFilterTag for easier removal logic
            function addFilterTag(label, value, type) {
                const tag = document.createElement('span');
                tag.className = 'filter-tag';
                tag.innerHTML = `${label}: ${value} <i class="fas fa-times"></i>`;
                tag.querySelector('i').addEventListener('click', function() {
                    if (type === 'search') {
                        searchInput.value = '';
                    } else if (type === 'sort') {
                        sortSelect.value = '';
                    } else {
                        // For actual filter keys (rating, gender, price, timeSlots)
                        delete currentFilters[type];
                        // Also reset the corresponding input in the filter modal
                        if (type === 'rating') document.getElementById('ratingFilter').value = '';
                        if (type === 'gender') document.getElementById('genderFilter').value = '';
                        if (type === 'minPrice') document.getElementById('minPrice').value = '';
                        if (type === 'maxPrice') document.getElementById('maxPrice').value = '';
                        if (type === 'timeSlots') {
                            document.querySelectorAll('input[name="timeSlots[]"]').forEach(cb => cb
                                .checked = false);
                        }
                    }
                    // After modifying, reload doctors with the updated state
                    const sortBy = sortSelect.value;
                    if (sortBy === 'distance') handleDistanceSort();
                    else loadDoctors(sortBy, null, null, currentFilters);
                });
                filterTags.appendChild(tag);
            }


            initializeFiltersFromUrl();
            const initialSortBy = sortSelect.value;
            const initialSearchTerm = searchInput.value.trim();


            if (initialSortBy === 'distance') {
                handleDistanceSort();
            } else if (initialSortBy || initialSearchTerm || Object.keys(currentFilters).length > 0) {
                loadDoctors(initialSortBy, null, null, currentFilters);
            } else {

                loadDoctors('', null, null, currentFilters);
            }

        });
    </script>

    <style>
        /* Custom CSS for improved UI */
        :root {
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --info-color: #17a2b8;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --white: #ffffff;
            --shadow-sm: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            --shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            --shadow-lg: 0 1rem 3rem rgba(0, 0, 0, 0.175);
            --border-radius: 0.5rem;
            --border-radius-lg: 0.75rem;
        }

        /* Search and Filter Section */
        .search-filter-card {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            padding: 2rem;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Search Input */
        .search-input-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            z-index: 2;
        }

        .search-input {
            height: 48px;
            padding-left: 2.5rem;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }

        .search-input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
            outline: none;
        }

        /* Sort Select */
        .sort-wrapper {
            position: relative;
        }

        .sort-select {
            height: 48px;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            font-size: 0.95rem;
            background-color: var(--white);
            transition: all 0.3s ease;
        }

        .sort-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }

        /* Filter Button */
        .btn-filter {
            height: 48px;
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            border-radius: var(--border-radius);
            color: var(--white);
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }

        .btn-filter:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            background: linear-gradient(135deg, #0056b3, var(--primary-color));
        }

        .filter-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: var(--danger-color);
            color: var(--white);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Results Info */
        .results-info {
            text-align: center;
            padding: 0.75rem;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: var(--border-radius);
            height: 48px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .results-count {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--primary-color);
            line-height: 1;
        }

        .results-text {
            font-size: 0.8rem;
            color: var(--secondary-color);
            line-height: 1;
        }

        /* Active Filters */
        .active-filters-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: var(--border-radius);
        }

        .active-filters-label {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }

        .filter-tags-container {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .filter-tag {
            background: var(--primary-color);
            color: var(--white);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-tag i {
            cursor: pointer;
            opacity: 0.8;
        }

        .filter-tag i:hover {
            opacity: 1;
        }

        .btn-clear-filters {
            background: none;
            border: 1px solid var(--danger-color);
            color: var(--danger-color);
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-clear-filters:hover {
            background: var(--danger-color);
            color: var(--white);
        }

        /* Doctor Cards */
        .doctor-card {
            transition: all 0.3s ease;
        }

        .doctor-card-inner {
            background: var(--white);
            border-radius: var(--border-radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .doctor-card-inner:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-color);
        }

        .doctor-photo {
            position: relative;
            overflow: hidden;
            height: 280px;
        }

        .doctor-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .doctor-card-inner:hover .doctor-image {
            transform: scale(1.05);
        }

        .doctor-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(0, 123, 255, 0.9);
            color: var(--white);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(10px);
            font-size: 1.1rem;
        }

        .doctor-info {
            padding: 1.5rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            text-align: center;
        }

        .doctor-name {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-color);
            margin-bottom: 0.5rem;
            line-height: 1.5rem;
            text-align: center;
        }

        .doctor-specialty {
            color: var(--secondary-color);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .rating-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .stars {
            color: var(--warning-color);
            font-size: 0.9rem;
        }

        .rating-text {
            color: var(--secondary-color);
            font-size: 0.85rem;
            font-weight: 500;
        }

        .doctor-footer {
            margin-top: auto;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .price {
            text-align: center;
        }

        .price-label {
            display: block;
            color: var(--secondary-color);
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .price-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--success-color);
        }

        .btn-book {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            border: none;
            color: var(--white);
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-book:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
            color: var(--white);
            background: linear-gradient(135deg, #0056b3, var(--primary-color));
        }

        /* No Results */
        .no-results-message {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-sm);
        }

        .no-results-icon {
            font-size: 4rem;
            color: var(--secondary-color);
            margin-bottom: 1.5rem;
            opacity: 0.5;
        }

        .no-results-message h4 {
            color: var(--dark-color);
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .no-results-message p {
            color: var(--secondary-color);
            margin-bottom: 2rem;
            font-size: 1.1rem;
        }

        /* Enhanced Filter Modal */
        .filter-modal .modal-content {
            border: none;
            border-radius: var(--border-radius-lg);
            box-shadow: var(--shadow-lg);
        }

        .filter-modal .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #0056b3);
            color: var(--white);
            border-bottom: none;
            padding: 1.5rem 2rem;
        }

        .filter-modal .modal-body {
            padding: 2rem;
        }

        .filter-group {
            margin-bottom: 2rem;
        }

        .filter-label {
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            font-size: 1rem;
        }

        .filter-group .form-select,
        .filter-group .form-control {
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding-top: 1px;
        }

        .filter-group .form-select:focus,
        .filter-group .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.1);
        }

        /* Price Range */
        .price-range-wrapper {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .price-input-group {
            flex: 1;
        }

        .price-input-group .price-label {
            display: block;
            font-size: 0.8rem;
            color: var(--secondary-color);
            margin-bottom: 0.25rem;
            font-weight: 500;
        }

        .price-separator {
            font-weight: 600;
            color: var(--secondary-color);
            font-size: 1.2rem;
        }

        /* Time Slots */
        .time-slots-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }

        .time-slot-item {
            position: relative;
        }

        .time-slot-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
        }

        .time-slot-label {
            display: block;
            padding: 1rem;
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .time-slot-label span {
            display: block;
            font-weight: 600;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }

        .time-slot-label small {
            color: var(--secondary-color);
            font-size: 0.8rem;
        }

        .time-slot-item input:checked+.time-slot-label {
            background: var(--primary-color);
            border-color: var(--primary-color);
            color: var(--white);
        }

        .time-slot-item input:checked+.time-slot-label span,
        .time-slot-item input:checked+.time-slot-label small {
            color: var(--white);
        }

        .time-slot-label:hover {
            border-color: var(--primary-color);
            background: rgba(0, 123, 255, 0.1);
        }

        .time-slot-item input:checked+.time-slot-label:hover {
            background: #0056b3;
        }

        /* Modal Footer */
        .filter-modal .modal-footer {
            padding: 1.5rem 2rem;
            border-top: 1px solid #e9ecef;
        }

        .filter-modal .modal-footer .btn {
            padding: 0.75rem 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 600;
            transition: all 0.3s ease;
        }

        /* Responsive Design */
        @media (max-width: 1199px) {
            .doctor-card {
                margin-bottom: 2rem;
            }
        }

        @media (max-width: 991px) {
            .search-filter-card {
                padding: 1.5rem;
            }

            .results-info {
                text-align: center;
                margin-top: 1rem;
            }

            .active-filters-wrapper {
                margin-top: 1rem;
            }

            .time-slots-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .search-filter-card {
                padding: 1rem;
            }

            .doctor-photo {
                height: 200px;
            }

            .doctor-info {
                padding: 1rem;
            }

            .price-range-wrapper {
                flex-direction: column;
                gap: 0.5rem;
            }

            .price-separator {
                display: none;
            }

            .filter-modal .modal-body {
                padding: 1rem;
            }
        }

        @media (max-width: 575px) {
            .btn-filter span {
                display: none;
            }

            .doctor-name {
                font-size: 1.1rem;
            }

            .price-amount {
                font-size: 1.3rem;
            }
        }

        .doctor-card-inner {
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .doctor-card[style*="display: none"] {
            animation: fadeOut 0.3s ease forwards;
        }

        .doctor-card[style*="display: block"] {
            animation: fadeIn 0.3s ease forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: scale(1);
            }

            to {
                opacity: 0;
                transform: scale(0.8);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.8);
            }

            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Custom scrollbar for modal */
        .modal-body::-webkit-scrollbar {
            width: 6px;
        }

        .modal-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }

        .modal-body::-webkit-scrollbar-thumb:hover {
            background: #0056b3;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
@endsection
