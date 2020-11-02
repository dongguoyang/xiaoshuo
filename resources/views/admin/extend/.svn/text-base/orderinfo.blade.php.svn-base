<div class="box">
    {{--    <hr style="border-color: #fff;margin: 0;">--}}


    <style>
        #grid-table5de27571e536a td > div {
            margin: 10px
        }

        #grid-table5de27571e536a td:nth-child(2) > div > div {
            margin: 10px auto;
            font-weight: bold;
        }

        #grid-table5de27571e536a td:first-child {
            width: 150px;
            vertical-align: middle;
        }

        #grid-table5de27571e536a td:nth-child(2) {
            width: 150px;
            text-align: left;
        }

        #grid-table5de27571e536a td:nth-child(3) {
            text-align: left;
        }

        #grid-table5de27571e536a td:nth-child(3) div {
            height: 40px;
            line-height: 40px;
        }

        #grid-table5de27571e536a td:nth-child(3) span {
            margin-right: 15px;
            margin-top: -3px;
        }
    </style>
    <div class="box-body table-responsive no-padding">
        <table class="table table-hover" id="">
            <thead>
            <tr>
                <th class="column-id">对账日期</th>
                <th class="column-title">渠道名称 / 页面名称</th>
                <th class="column-value">原文访问人数</th>
                <th class="column-status">交易笔数</th>
                <th class="column-updated_at">充值金额</th>

            </tr>
            </thead>


            <tbody>
            @foreach($data as $key=> $v)
                <tr>

                    <td class="column-id">
                        {{$key}}
                    </td>
                    <td class="column-title">
                        {{$title}}
                    </td>
                    <td class="column-value">
                        {{$v['user']}}
                    </td>
                    <td class="column-status">
                        {{$v['recharge']}}
                    </td>
                    <td class="column-updated_at">
                        {{$v['money']}}
                    </td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>