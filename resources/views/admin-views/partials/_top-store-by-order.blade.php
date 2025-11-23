<div class="card-header gap-10">
    <h4 class="d-flex align-items-center text-capitalize gap-10 mb-0">
        <img src="{{dynamicAsset(path: 'public/assets/back-end/img/most-popular-store-icon.png')}}" alt="">
        {{translate('most_Popular_Stores')}}
    </h4>
</div>
<div class="card-body">
    @if($top_store_by_order_received)
        <div class="d-flex flex-column gap-10">
            @foreach($top_store_by_order_received as $key=>$item)
                @if(isset($item->seller->shop))
                    @php($shop=$item->seller->shop)
                    <a href="{{route('admin.vendors.view',$item['seller_id'])}}" class="border p-20 rounded d-flex align-items-center gap-2 justify-content-between">
                        <div class="d-flex align-items-center gap-10">
                            <img width="35" src="{{getStorageImages(path: $shop->image_full_url,type:'backend-basic')}}" class="rounded-circle aspect-1" alt="">
                            <h5 class="mb-0">{{$shop['name']??'Not exist'}}</h5>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="shop-sell mb-0">{{$item['count']}}</h5>
                            <img src="{{dynamicAsset(path: 'public/assets/back-end/img/love.png')}}" alt="">
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    @else
        <div class="text-center">
            <p class="text-muted">{{translate('no_Top_Selling_Products')}}</p>
            <img class="w-75" src="{{dynamicAsset(path: 'public/assets/back-end/img/no-data.png')}}" alt="">
        </div>
    @endif
</div>
