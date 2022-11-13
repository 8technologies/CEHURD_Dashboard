<?php
use App\Models\Utils;
?><style>
    .ext-icon {
        color: rgba(0, 0, 0, 0.5);
        margin-left: 10px;
    }

    .installed {
        color: #00a65a;
        margin-right: 10px;
    }

    .card {
        border-radius: 5px;
    }
</style>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3>
            <b>Recent cases</b>
        </h3>
        <div>
            <a href="{{ url('/cases') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-0">
        <!--begin::Table container-->
        <div class="table-responsive">
            <!--begin::Table-->
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4">
                <!--begin::Table head-->
                <thead>
                    <tr class="fw-bolder text-muted">
                        <th class="min-w-200px">Victim</th>
                        <th class="min-w-150px">Case</th>
                        <th class="min-w-150px">Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($items as $i)
                        <?php
                        //dd($i->photo_url);
                        ?>
                        <tr>
                            {{-- 
    
    --}}
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <img src="{{ $i->photo_url }}" class="img-fluid" width="60" alt="">
                                    </div>
                                    <div class="d-flex justify-content-start flex-column pl-3">
                                        <a href="#" style="color: black; font-weight: 600;"
                                            class="text-dark fw-bolder text-hover-primary fs-6">{{ $i->applicant_name }}</a>
                                        <span
                                            class="text-muted fw-bold text-muted d-block fs-7">{{ $i->sex }}</span>
                                        <span class="text-muted fw-bold text-muted d-block fs-7">
                                            {{ Utils::get('App\Models\Location', $i->sub_county)->name_text }}
                                        </span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <b class="text-dark fw-bold  d-block fs-7"
                                    style="color: black">{{ Str::of($i->title)->limit(40) }}</b>
                                <span class="fw-bold text-primary d-block fs-7">{{ $i->case_category }}</span>
                            </td>
                            <td class="text-end">
                                <span class="badge bg-{{ Utils::tell_suspect_status_color($i->status) }}">
                                    {{ $i->status }}
                                </span>
                            </td>
                            <td>
                                <div class=" justify-content-end text-right ">
                                    <a href="{{ url("/cases/{$i->id}") }}" title="View"
                                        class="btn btn-icon btn-bg-light  text-dark  me-1 p-0 px-2 m-0"
                                        style="font-size: 16px;">

                                        <i class="fa fa-eye"></i>

                                        <span>View</span>
                                        <!--end::Svg Icon-->
                                    </a><br>
                                    <a href="{{ url("/cases/{$i->id}") }}/edit" title="View"
                                        class="btn btn-icon btn-bg-light text-primary   me-1 p-0 px-2 m-0"
                                        style="font-size: 16px;">

                                        <i class="fa fa-edit"></i>

                                        <span class="ml-2">Edit</span>
                                    </a>


                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>

            </table>
            <!--end::Table-->
        </div>
        <!--end::Table container-->
    </div>
    <!--begin::Body-->
</div>
