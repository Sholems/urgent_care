
@if($doctors && count($doctors) > 0)
    @foreach ($doctors as $doctor)
        @php
            // Safely get the first category name
            $firstCategory = 'Specialist';
            if (!empty($doctor->categories)) {
                if (is_array($doctor->categories) && count($doctor->categories) > 0) {
                    $firstCategory = is_array($doctor->categories[0]) 
                        ? ($doctor->categories[0]['name'] ?? $doctor->categories[0])
                        : $doctor->categories[0];
                } elseif (is_string($doctor->categories)) {
                    $decoded = json_decode($doctor->categories, true);
                    $firstCategory = is_array($decoded) && count($decoded) > 0 ? $decoded[0] : $doctor->categories;
                }
            }
            
            // Calculate average rating
            $avgRating = $doctor->avg_rating ?? ($doctor->reviews->avg('rating') ?? $doctor->rating ?? 0);
            $reviewCount = $doctor->reviews->count() ?? 0;
            
            // Ensure numeric values
            $price = $doctor->price ?? 100;
            $experience = $doctor->experience ?? 0;
        @endphp
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4 doctor-card"
            data-name="{{ strtolower($doctor->name ?? '') }}"
            data-specialty="{{ strtolower($firstCategory) }}"
            data-rating="{{ number_format($avgRating, 1) }}" 
            data-price="{{ $price }}"
            data-gender="{{ strtolower($doctor->gender ?? 'male') }}"
            data-experience="{{ $experience }}"
            data-latitude="{{ $doctor->latitude ?? 0 }}"
            data-longitude="{{ $doctor->longitude ?? 0 }}"
            data-availability='{{ json_encode($doctor->availability ?? []) }}'>
            
            <div class="doctor-card-inner">
                <!-- Doctor Photo -->
                <div class="doctor-photo">
                    @if ($doctor->photo)
                        <img src="{{ $doctor->photo }}" alt="{{ $doctor->name }}" class="doctor-image">
                    @else
                        <img src="{{ asset('img/800x800.png') }}" alt="{{ $doctor->name }}" class="doctor-image">
                    @endif
                    <div class="doctor-badge">
                        <i class="fas fa-user-md"></i>
                    </div>
                </div>

                <!-- Doctor Info -->
                <div class="doctor-info">
                    <h4 class="doctor-name">{{ $doctor->name ?? 'Dr. Unknown' }}</h4>
                    <p class="doctor-specialty">{{ $firstCategory }}</p>
                    
                    <!-- Rating -->
                    <div class="rating-wrapper">
                        <div class="stars">
                            @for ($i = 1; $i <= 5; $i++)
                                @if ($i <= floor($avgRating))
                                    <i class="fas fa-star"></i>
                                @elseif ($i <= ceil($avgRating) && $avgRating > floor($avgRating))
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="rating-text">
                            {{ number_format($avgRating, 1) }} ({{ $reviewCount }} reviews)
                        </span>
                    </div>
                    
                    <!-- Experience -->
                    @if($experience > 0)
                        <div class="doctor-experience mb-2">
                            <i class="fas fa-briefcase-medical me-1"></i>
                            {{ $experience }} Years Experience
                        </div>
                    @endif
                    
                    <!-- Location -->
                    @if(!empty($doctor->city))
                        <div class="doctor-location mb-2">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            {{ $doctor->city }}
                        </div>
                    @endif

                    <!-- Doctor Footer -->
                    <div class="doctor-footer">
                        <!-- Price -->
                        <div class="price">
                            <span class="price-label">Consultation Fee</span>
                            <div class="price-amount">${{ number_format($price) }}</div>
                        </div>
                        
                        <!-- View Profile Button -->
                        <a href="{{ route('doctor', $doctor->id) }}" class="btn btn-book">
                            <i class="fas fa-eye me-1"></i>View Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@else
    <div class="col-12">
        <div class="no-results-message text-center">
            <div class="no-results-icon">
                <i class="fas fa-search"></i>
            </div>
            <h4>No doctors found</h4>
            <p>Try adjusting your search criteria or filters to find more doctors</p>
        </div>
    </div>
@endif
