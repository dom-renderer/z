@extends('layouts.app-master')

@push('css')
<style>
    .gallery img { width: 150px; cursor: pointer; margin: 5px; }
    .lightbox { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); text-align: center; }
    .lightbox img { max-width: 80%; max-height: 80%; margin-top: 5%; transition: transform 0.3s; }
    .controls { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); }
    .controls button { margin: 5px; padding: 10px; cursor: pointer; }
    .close { position: absolute; top: 10px; right: 20px; font-size: 30px; color: white; cursor: pointer; }
    .prev, .next { position: absolute; top: 50%; transform: translateY(-50%); font-size: 24px; color: white; background: rgba(0,0,0,0.5); border: none; padding: 10px; cursor: pointer; }
    .prev { left: 10px; display: none; }
    .next { right: 10px; display: none; }
</style>
@endpush


@php

if (is_string($task->data)) {
    $data = json_decode($task->data, true);
} else if (is_array($task->data)) {
    $data = $task->data;
} else {
    $data = [];
}

$groupedData = [];
foreach ($data as $item) {
    $groupedData[$item->className][] = $item;
}

$varients = \App\Helpers\Helper::categorizePoints($task->data ?? []);

$total = count(\App\Helpers\Helper::selectPointsQuestions($task->data));
$toBeCounted = $total - count($varients['na']);

$failed = abs(count(array_column($varients['negative'], 'value')));
$achieved = $toBeCounted - abs($failed);

if ($failed <= 0) {
    $achieved = array_sum(array_column($varients['positive'], 'value'));
}

if ($toBeCounted > 0) {
    $percentage = ($achieved / $toBeCounted) * 100;
} else {
    $percentage = 0;
}

$hasImages = false;

$globalCounter = new \stdClass();
$globalCounter->value = 0;

$isPointChecklist = \App\Helpers\Helper::isPointChecklist($task->form);
@endphp

@section('content')

    <div class="bg-light p-4 rounded">

        <div class="container-for-data">
            <div class="bg-light p-4 rounded">

                <table class="table table-bordered table-stripped gallery">
                    <tbody>
                        @forelse ($groupedData as $className => $fields)
                        <tr>
                            @php  
                                $label = Helper::getQuestionField($fields);
                            @endphp
                            <td>{!! $label !!}</td>
                            
                                @foreach ($fields as $field)
                                    @if(property_exists($field, 'isFile') &&  $field->isFile)
                                        @if(is_array($field->value))
                                        <td> 
                                            @foreach ($field->value as $thisImg)
                                                @php 
                                                    $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                                                    $hasImages = true;
                                                @endphp
                                                <img data-index="{{ $globalCounter->value++ }}" class="thumbnail" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                            @endforeach
                                        </td>
                                        @else
                                        <td> 
                                            @php 
                                                $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                                                $hasImages = true;
                                            @endphp
                                            <img data-index="{{ $globalCounter->value++ }}" class="thumbnail" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                                        </td>
                                        @endif
                                    @else
                                        @if(property_exists($field, 'value_label'))
                                            @if($isPointChecklist)
                                                @if(is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!} ({{ $field->value }}) </td>
                                                @endif
                                            @else
                                                @if(is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!} </td>
                                                @endif
                                            @endif
                                        @else
                                            @if(is_array($field->value))
                                                <td> {!! implode(',', $field->value) !!} </td>
                                            @else
                                                <td> {!! $field->value !!} </td>
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                        </tr>
                        @empty
                        <tr>
                            <td>
                                No Data Found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                @forelse (\App\Helpers\Helper::getCustomFormListing($task->form) as $rowOfCustomForm)
                    @php
                        $dataToIterate = resolve(\App\Helpers\Helper::$customFormWithEloquent[$rowOfCustomForm])->select(\App\Helpers\Helper::$customFormFields[$rowOfCustomForm])->where('task_id', $task->id)->get();
                    @endphp

                    <hr>
                    <h3>
                        {{ \App\Helpers\Helper::$customFormTitle[$rowOfCustomForm] }}
                    </h3>

                    <table class="table table-resposive table-striped table-bordered">
                        <thead>
                            <tr>
                                @forelse ($dataToIterate as $dataToIterateKey => $dataToIterateValue)
                                    @foreach (array_keys($dataToIterateValue->getOriginal()) as $dataToIterateKeyRow)
                                        <th> {{ ucwords(str_replace(['_id', '_'], ' ', $dataToIterateKeyRow)) }} </th>
                                    @endforeach
                                @break
                                @empty
                                @endforelse
                            </tr>
                        </thead>
                        <tbody>
                                @forelse ($dataToIterate as $dataToIterateValue)
                                    <tr>
                                        @foreach ($dataToIterateValue->toArray() as $dataToIterateValueRow)
                                            <td> {{ $dataToIterateValueRow }} </td>
                                        @endforeach
                                    </tr>
                                @empty
                                @endforelse
                        </tbody>
                    </table>


                @empty
                @endforelse

                @if($isPointChecklist)
                <table class="table table-striped table-bordered">
                    <tbody>
                        <tr>
                            <td>Total Questions</td>
                            <td>{{ $total }}</td>
                        </tr>
                        <tr>
                            <td>Passed</td>
                            <td>{{ $achieved }}</td>
                        </tr>
                        <tr>
                            <td>Failed</td>
                            <td>{{ count($varients['negative']) }}</td>
                        </tr>
                        <tr>
                            <td>N/A</td>
                            <td>{{ count($varients['na']) }}</td>
                        </tr>
                        <tr>
                            <td>Percentage</td>
                            <td>{{ number_format($percentage, 2) }}%</td>
                        </tr>
                        <tr>
                            <td>Final Result</td>
                            <td>{{ $percentage > 80 ? "Pass" : "Fail" }}</td>
                        </tr>
                    </tbody>
                </table>
                @endif
            </div> 
        </div>
        
    </div> 


    @if($hasImages)
    <div class="lightbox">
        <span class="close">&times;</span>
        <button class="prev">&#10094;</button>
        <img id="lightbox-img" src="">
        <button class="next">&#10095;</button>
        <div class="controls">
            <button class="btn btn-sm btn-secondary" id="zoom-in">Zoom In</button>
            <button class="btn btn-sm btn-secondary" id="zoom-out">Zoom Out</button>
            <button class="btn btn-sm btn-secondary" id="download">Download</button>
        </div>
    </div>
    @endif
@endsection


@push('js')
<script>
    
    $(document).ready(function($){

        let currentIndex = 0;
        let scale = 1;
        let isDragging = false;
        let startX = 0, startY = 0;
        let moveX = 0, moveY = 0;
        let images = $(".thumbnail").map(function() { return $(this).attr("src"); }).get();

        function showLightbox(index) {
            currentIndex = index;
            scale = 1;
            resetImage();
            $("#lightbox-img").attr("src", images[currentIndex]);
            $(".lightbox").fadeIn();
            updateNavButtons();
        }

        function updateNavButtons() {
            $(".prev").toggle(currentIndex > 0);
            $(".next").toggle(currentIndex < images.length - 1);
        }

        $(".thumbnail").click(function() {
            showLightbox($(this).data('index'));
        });

        $(".close").click(function() { $(".lightbox").fadeOut(); });

        $(".prev").click(function() {
            if (currentIndex > 0) {
                showLightbox(currentIndex - 1);
            }
        });

        $(".next").click(function() {
            if (currentIndex < images.length - 1) {
                showLightbox(currentIndex + 1);
            }
        });

        $("#zoom-in").click(function() {
            scale += 0.2;
            applyTransform();
            if (scale > 1) {
                $("#lightbox-img").css("cursor", "grab");
            }
        });

        $("#zoom-out").click(function() {
            if (scale > 1) {
                scale -= 0.2;
                if (scale <= 1) {
                    resetImage();
                } else {
                    applyTransform();
                }
            }
        });

        $("#download").click(function() {
            let link = document.createElement('a');
            link.href = images[currentIndex];
            link.download = 'image.jpg';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        $("#lightbox-img").on("mousedown", function(e) {
            if (scale > 1) {
                isDragging = true;
                startX = e.clientX - moveX;
                startY = e.clientY - moveY;
                $(this).css("cursor", "grabbing");
            }
        });

        $(document).on("mousemove", function(e) {
            if (isDragging) {
                moveX = e.clientX - startX;
                moveY = e.clientY - startY;
                applyTransform();
            }
        });

        $(document).on("mouseup", function() {
            isDragging = false;
            $("#lightbox-img").css("cursor", "grab");
        });

        function applyTransform() {
            $("#lightbox-img").css("transform", `scale(${scale}) translate(${moveX}px, ${moveY}px)`);
        }

        function resetImage() {
            scale = 1;
            moveX = 0;
            moveY = 0;
            $("#lightbox-img").css({
                "transform": `scale(1) translate(0px, 0px)`,
                "cursor": "default"
            });
        }

    });
 </script>  
@endpush