@extends('backend.app')
@section('title', 'Membership Spotlights')
@section('content')
    <div class="container-fluid py-4">

        <!-- Header -->
        <div
            class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <h2 class="fw-bold m-0">Membership Spotlights</h2>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('admin.members_spotlight.index', ['status' => 'approved']) }}" class="btn btn-secondary">
                    Applicants <span
                        class="badge bg-light text-dark">{{ $approved }}</span>
                </a>

                <a href="{{ route('admin.members_spotlight.index', ['status' => 'pending']) }}" class="btn btn-secondary">
                    Archived <span class="badge bg-light text-dark">{{ $pending }}</span>
                </a>
            </div>
        </div>

        <!-- Search bar -->
        <form method="GET" action="{{ route('admin.members_spotlight.index') }}">
            <div class="input-group mb-4">

                <input type="text" name="name" class="form-control" placeholder="Search applicants...">
                <button class="btn btn-secondary" type="submit" value="{{ old('name') }}">
                    <i class="fas fa-search"></i>
                </button>

            </div>
        </form>

        <!-- Cards -->
        <div class="row">
            <div class="col-md-12 col-lg-6 col-xl-12 mb-4">
                <div class="card shadow-sm h-100">
                    @foreach ($applications as $application)
                        <div class="row g-0 border border-light mb-1 rounded-5 overflow-hidden">
                            <!-- Image section -->
                            <div class="col-md-2 d-none d-md-block">
                                <div class="position-relative h-100">
                                    <a href="{{ asset($application->image ?? 'backend/images/placeholder/image_placeholder.png') }}"
                                        data-lightbox="roadtrip" target="_blank"><img
                                            src="{{ asset($application->image ?? 'backend/images/placeholder/image_placeholder.png') }}"
                                            class="img-fluid rounded-start w-100 h-100 object-fit-cover"
                                            alt="...">,</a>
                                </div>
                            </div>

                            <!-- Content section -->
                            <div class="col-md-10 col-12">
                                <div class="card-body d-flex flex-column justify-content-between h-100">

                                    <!-- Top Row: Date Right Side -->
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="card-title mb-1">{{ $application->shop_name }}</h5>
                                            <p class="card-text"><small class="text-muted">Owner:
                                                    {{ $application->name }}</small></p>
                                        </div>
                                        <div class="text-md-end">
                                            <p class="text-muted mb-0">Request Date:
                                                {{ $application->created_at->format('d F Y') }}</p>
                                        </div>
                                    </div>

                                    <!-- Description -->
                                    <p class="card-text mt-3">
                                        {{ $application->shop_description }}
                                    </p>
                                    <!-- Action Buttons -->
                                    <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2"
                                        id="product-actions-{{ $application->id }}">
                                        <a href="{{ route('admin.members_spotlight.show', $application->id) }}" class="btn btn-sm"
                                            style="background-color:#D4E2CB; color: #274F45 ">More Details <i
                                                class="fa-solid fa-arrow-right"></i></a>
                                    </div>
                                </div>
                            </div>

                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>
@endsection

@push('script')
   
@endpush
