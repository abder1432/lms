<section id="latest-area" class="latest-area-section {{isset($pt) ? $pt : ''}}">
    <div class="container">
        <div class="row">
            <div class="col-md-12">
                <div class="latest-area-content  ">
                    <div class="section-title-2 mb65 headline text-left">
                        <h2>@lang('labels.frontend.layouts.partials.latest_news_blog')</h2>
                    </div>
                    <div class="latest-news-posts row">
                        @if(count($news) > 0)


                            @foreach($news->take(3) as  $item)
                            <div class="col-md-4">
                                <div class="latest-news-area">
                                    @if($item->image != null)
                                        <div class="latest-news-thumbnile relative-position"
                                             style="background-image: url('{{asset("storage/uploads/".$item->image)}}');">
                                            <div class="hover-search">
                                                {{--<i class="fas fa-search"></i>--}}
                                            </div>
                                            <div class="blakish-overlay"></div>
                                        </div>
                                    @endif

                                    <div class="date-meta">
                                        <i class="fas fa-calendar-alt"></i> {{$item->created_at->format('d-m')}}
                                    </div>
                                    <h3 class="latest-title bold-font"><a
                                                href="{{route('blogs.index',['slug' => $item->slug.'-'.$item->id])}}">{{$item->title}}</a>
                                    </h3>
                                    <h3 class="latest-title text-primary"><a
                                                href="{{route('blogs.category',['category' => $item->category->slug])}}">{{$item->category->name}}</a>
                                    </h3>
                                    <div class="course-viewer ul-li">
                                        <ul>
                                            {{--<li><a href=""><i class="fas fa-user"></i> 1.220</a></li>--}}
                                            @if($item->comments->count() > 1)
                                                <li><a href=""><i
                                                                class="fas fa-comment-dots"></i>{{ $item->comments->count() }}
                                                    </a></li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
 
                        @endforeach
                    @endif

                    <!-- /post -->

             
                    </div>
                </div>
            </div>



            <div class="view-all-btn bold-font">
                <a href="{{route('blogs.index')}}">@lang('labels.frontend.layouts.partials.view_all_news') <i class="fas fa-chevron-circle-right"></i></a>
            </div>

        </div>
    </div>
</section>