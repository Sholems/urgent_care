
@if($doctors && count($doctors) > 0)
    @foreach ($doctors as $doctor)
        @php
            $categories = $doctor->categories;
            if (is_string($categories)) {
                $categories = json_decode($categories, true) ?? [$categories];
            }
            $category = data_get($categories, '0.name', data_get($categories, '0'));
            $firstCategory = is_string($category) ? trim($category) : '';
            $price = $doctor->price ?? 100;
            $experience = $doctor->experience ?? 0;
        @endphp
        
        <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 mb-4 doctor-card"
            data-name="{{ strtolower($doctor->name ?? '') }}"
            data-specialty="{{ strtolower($firstCategory) }}"
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
                </div>

                <!-- Doctor Info -->
                <div class="doctor-info">
                    <h4 class="doctor-name">{{ $doctor->name ?? 'Dr. Unknown' }}</h4>
                    @if ($firstCategory !== '')
                        <p class="doctor-specialty">{{ $firstCategory }}</p>
                    @endif
                    
                    
                    
                    <!-- Experience -->
                    @if($experience > 0)
                        <div class="doctor-experience mb-2">
                            <i class="fas fa-briefcase-medical me-1"></i>
                            {{ trans_choice(':count year of experience|:count years of experience', $experience) }}
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
                            <div class="price-amount">${{ number_format($doctor->price) }}</div>
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
