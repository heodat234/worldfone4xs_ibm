const ENV = <?php if(isset($ENV)) echo $ENV; else echo "{}" ?>;
const convertExtensionToAgentname = <?= $convertExtensionToAgentname ?>;

const HELPER = {
    loaderHtml: `<?php if(!empty($loader_layer)) echo '<div class="loader-container"><div class="loader"></div></div>'; else echo ""; ?>`,
    bsColors: ["success","warning","info","danger","primary","default"]
};

const KENDO = {
    noRecords: "@NO DATA@",
    filterable: {
        messages: {
            and: "@and@",
            or: "@or@",
            filter: "@Apply@",
            clear: "@Clear@",
            info: "@Filter by@: ",
        },
        operators: {
            string: {
                contains: "@Contains@",
                eq: "@Equal to@",
                neq: "@Not equal to@",
                doesnotcontain: "@Doesn't contain@",
                isempty: "@Empty@",
                isnotempty: "@Not empty@",
                startswith: "@Starts with@",
                doesnotstartwith: "@Does not start@",
                endswith: "@Ends with@",
                doesnotendwith: "@Does not end@",
                isnull: "Null",
                isnotnull: "@Not null@"
            },
            number: {
                eq: "@Equal to@",
                neq: "@Not equal to@",
                gte: "@Greater than or equal to@",
                lte: "@Less than or equal to@",
                gt: "@Greater than@",
                lt: "@Less than@",
                isempty: "@Empty@",
                isnotempty: "@Not empty@",
                isnull: "Null",
                isnotnull: "@Not null@"
            },
            date: {
                gte: "@After day@",
                lte: "@Before day@"
            }
        }
    },
    pageableMessages: {
        display: "{0} @to@ {1} @from@ {2} @items@",
        empty: "@No data@",
        page: "@Page@",
        of: "@from@ {0} @page@",
        itemsPerPage: "@items@ @per@ @page@",
        first: "@First page@",
        last: "@Last page@",
        next: "@Next page@",
        previous: "@Previous page@",
        refresh: "@Refresh@",
        morePages: "@More pages@"
    },
    schedulerMessages: {
        allDay: "@Full day@",
        today: "@Current date@",
        next: "@Next@",
        previous: "@Previous@",
        save: "@Save@",
        cancel: "@Cancel@",
        destroy: "@Delete@",
        deleteWindowTitle: "@Delete@ @note@",
        editable: {
            confirmation: "@Are you sure@?"
        },
        editor: {
            editorTitle: "@Edit@ @note@",
            title: "@Title@",
            allDayEvent: "@Full day@",
            description: "@Description@",
            start: "@Start@",
            end: "@End@",
            repeat: "@Repeat@",
            timezone: "@Timezone@",
            timezoneEditorButton: "@Change@",
        }
    }
};

const NOTIFICATION = {
    operationSuccess: "@Good! Your operation was successful@",
    disconnectServer: "@You was disconnected@",
    sessionExpire: "@Your session was expired@",
    checkSure: "@Are you sure@",
    callFor: "@Call for@",
    error: "@Error@",
    detail: "@Detail@",
    Transfer: "@Transfer@",
    thiscall: "@this call@",
    Save: "@Save@",
    success: "@success@",
    closeThisForm: "@Close this form@",
    select: "@Select@",
    Hangup: "@Hangup@",
    Refresh: "@Refresh@",
    Minimize: "@Minimize@",
    Maximize: "@Maximize@",
    Close: "@Close@"
};