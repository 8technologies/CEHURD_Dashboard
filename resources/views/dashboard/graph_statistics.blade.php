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

    .case-item:hover {
        background-color: rgb(254, 254, 254);
    }

    .my-counter {
        color: rgb(16, 15, 15);
        font-size: 4rem;
        font-weight: 600;
        line-height: 3.7rem;
        padding-top: 1rem;
    }

    .my-title {
        color: rgb(105, 98, 98);
        font-size: 2rem;
        font-weight: 600;
        line-height: 2rem;
        font-family: Poppins, Helvetica, sans-serif;
    }

    .my-item {
        border: dashed rgb(194, 186, 185) .5rem;
        border-radius: 2rem;
        background-color: #fcf4f4;
    }
</style>
<div class="card  mb-4 mb-md-5 border-0">
    <!--begin::Header-->
    <div class="d-flex justify-content-between px-3 px-md-4 ">
        <h3>
            <b>Statistics</b>
        </h3>
        <div>
            <a href="{{ url('/cases') }}" class="btn btn-sm btn-primary mt-md-4 mt-4">
                View All
            </a>
        </div>
    </div>
    <div class="card-body py-2 py-md-3">

        <div class="row">
            @php
                $x = 0;
            @endphp
            @foreach ($data as $j => $i)
                @php
                    $x++;
                    if ($x > 9) {
                        break;
                    }
                @endphp
                <div class="col-6 col-md-4">
                    <div class=" my-item my-1    p-3 p-md-4 mb-3 ">
                        <img width="57%" class="img-fluid" src="{{ Utils::get_category_pic($j) }}" alt="">
                        <p class="my-counter">{{ number_format($i) }}</p>
                        <p class="my-title">{{ $j }}</p>
                    </div>
                </div>
            @endforeach
        </div>

    </div>
</div>
