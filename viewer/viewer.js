var $reportContainer = jQuery('#reportContainer');

var $restUrl = $reportContainer.data('restUrl');

jrio.config({
    server: $restUrl + "jrio/v1",
    scripts: $restUrl + "jrio-client/v1/optimized-scripts",
    theme: {
        href: $restUrl + "jrio-client/v1/themes/default"
    },
    locale: "en_US"
});

jrio(function(jrioClient) {
    var executionIdParam = "executionId",
        reportUriParam = "jr_report_uri",
        ignorePaginationParam = "ignorePagination",
        reportLocaleParam = "reportLocale",
        reportTimeZoneParam = "reportTimeZone",
        pageParam = "page",
        anchorParam = "anchor",

        report,
        pagesNo = null,
        currentPage = 1,
        urlParams = {},
        reportParams = {},
        finalParams = {},
        scale_index = 1;

    // extract URL parameters & prepare the report ones
    if (window.location.search) {
        urlParams = window.lodash.chain(window.location.search.substring(1))
            .split("&")
            .map(window.lodash.partial(window.lodash.split, window.lodash, "=", 2))
            .reduce(function(r, p) {
                var pname = decodeURIComponent(p[0]),
                    pval = decodeURIComponent(p[1]);
                if (r[pname]) {
                    if (!window.lodash.isArray(r[pname])) {
                        r[pname] = [ r[pname] ];
                    }
                    r[pname].push(pval);
                } else {
                    r[pname] = pval;
                }
                return r;
            }, {})
            .value();

        reportParams = window.lodash.omit(urlParams,[
            executionIdParam,
            reportUriParam,
            ignorePaginationParam,
            reportLocaleParam,
            reportTimeZoneParam,
            pageParam,
            anchorParam]);
    }

    var reportConfig = {
        container: "#reportContainer",

        error: failHandler,
        events: {
            changeTotalPages: totalPagesHandler,
            changePagesState: pageStateChangeHandler,
            canUndo: undoHandler,
            canRedo: redoHandler,
            reportCompleted: function(status, error) {
                if (status === "failed") {
                    failHandler(error);
                }
            }
        },

        linkOptions: {
            events: { click: clickHandler }
        }
    };

    if (urlParams[executionIdParam]) {
        reportConfig.resource = {
            executionId: urlParams[executionIdParam]
        }
    } else {
        // convert non-array key values to array
        if (!window.lodash.isEmpty(reportParams)) {
            window.lodash.each(reportParams, function(val, key) {
                finalParams[key] = window.lodash.isArray(val) ? val : [ val ];
            });
        }

        reportConfig.resource = urlParams[reportUriParam];
        reportConfig.params = finalParams;

        if (urlParams[ignorePaginationParam] == "true") {
            reportConfig.ignorePagination = true;
        } else if (urlParams[ignorePaginationParam] == "false") {
            reportConfig.ignorePagination = false;
        }//ignore other ignorePaginationParam values

        if (urlParams[reportLocaleParam]) {//TODO default to userLocale?
            reportConfig.reportLocale = urlParams[reportLocaleParam];
        }
        if (urlParams[reportTimeZoneParam]) {
            reportConfig.reportTimeZone = urlParams[reportTimeZoneParam];
        }

        if (urlParams[pageParam] && urlParams[anchorParam]) {
            currentPage = urlParams[pageParam];
            reportConfig.pages = {
                pages: urlParams[pageParam],
                anchor: urlParams[anchorParam]
            };
        } else if (urlParams[pageParam]) {
            currentPage = urlParams[pageParam];
            reportConfig.pages = urlParams[pageParam];
        } else if (urlParams[anchorParam]) {
            reportConfig.pages = {
                anchor: urlParams[anchorParam]
            };
        }
    }

    report = jrioClient.report(reportConfig);

    function totalPagesHandler(totalPages) {
        if (totalPages == null || totalPages == 0) {
            alert("The report is empty!");
        }

        pagesNo = totalPages;
        updatePaginationButtons();
    }

    function pageStateChangeHandler(pageIndex) {
        currentPage = pageIndex;
        updatePaginationButtons();
    }

    function undoHandler(undoPossible) {
        if (undoPossible) {
            jQuery("#undo").prop("disabled", false);
            jQuery("#undoAll").prop("disabled", false);
        } else {
            jQuery("#undo").prop("disabled", true);
            jQuery("#undoAll").prop("disabled", true);
        }
    }

    function redoHandler(redoPossible) {
        if (redoPossible) {
            jQuery("#redo").prop("disabled", false);
        } else {
            jQuery("#redo").prop("disabled", true);
        }
    }

    function clickHandler(ev, hyperlinkData) {
        if ("ReportExecution" === hyperlinkData.type) {
            var hParams = hyperlinkData.parameters,
                hReportParam = "_report";
            if (hParams && hParams[hReportParam]) {
                var href = "viewer?" + reportUriParam + "=" + hParams[hReportParam],
                    urlParams = window.lodash.omit(hParams, hReportParam);

                window.lodash.each(urlParams, function(value, key) {
                    href += "&" + key + "=" + value;
                });

                if ("Self" === hyperlinkData.target) {
                    window.location = href;
                } else if ("Blank" === hyperlinkData.target) {
                    window.open(href, "_blank");
                }
            }
        } else if ("LocalAnchor" === hyperlinkData.type) {
            if ("Self" === hyperlinkData.target) {
                changeCurrentReportPage({ anchor: hyperlinkData.anchor });
            } else if ("Blank" === hyperlinkData.target) {
                window.open(buildViewerUrl({ anchor: hyperlinkData.anchor }), "_blank");
            }
        } else if ("LocalPage" === hyperlinkData.type) {
            if ("Self" === hyperlinkData.target) {
                currentPage = hyperlinkData.pages;
                changeCurrentReportPage(currentPage);
            } else if ("Blank" === hyperlinkData.target) {
                window.open(buildViewerUrl({ page: currentPage }), "_blank");
            }
        } else if ("Reference" === hyperlinkData.type) {
            if ("Self" === hyperlinkData.target) {
                window.location.href = hyperlinkData.href;
            } else if ("Blank" === hyperlinkData.target) {
                window.open(hyperlinkData.href, "_blank");
            }
        } else if ("RemoteAnchor" === hyperlinkData.type) {
            if ("Self" === hyperlinkData.target) {
                window.location.href = hyperlinkData.href;
            } else if ("Blank" === hyperlinkData.target) {
                window.open(hyperlinkData.href, "_blank");
            }
        }
    }

    function changeCurrentReportPage(pagesParam) {
        report.pages(pagesParam)
            .run()
            .done(updatePaginationButtons)
            .fail(failHandler);
    }

    function buildViewerUrl(params) {
        var href = "viewer?" + reportUriParam + "=" + urlParams[reportUriParam];
        window.lodash.each(params, function(value, key) {
            href += "&" + key + "=" + value;
        });

        return href;
    }

    function failHandler(err) {
        alert(err);
    }

    function updatePaginationButtons() {
        if (pagesNo == null) {
            jQuery("#page_next, #page_last").prop("disabled", true);
        }
        else if (pagesNo > 1 && currentPage < pagesNo) {
            jQuery("#page_next, #page_last").prop("disabled", false);
        } else {
            jQuery("#page_next, #page_last").prop("disabled", true);
        }

        if (pagesNo == null || pagesNo == 0) {
            jQuery("#totalPagesNo").text("0");
            jQuery("#page_current").prop("disabled", true);
            jQuery("#page_current").val("");

            jQuery("#export").prop("disabled", true);

            jQuery("#zoom_in").prop("disabled", true);
            jQuery("#zoom_out").prop("disabled", true);
            jQuery("#zoom_value_button").prop("disabled", true);
        } else {
            jQuery("#totalPagesNo").text(pagesNo);
            jQuery("#page_current").prop("disabled", false);
            jQuery("#page_current").val(currentPage);

            jQuery("#export").prop("disabled", false);

            jQuery("#zoom_in").prop("disabled", false);
            jQuery("#zoom_out").prop("disabled", false);
            jQuery("#zoom_value_button").prop("disabled", false);
        }

        if (currentPage == 1) {
            jQuery("#page_first, #page_prev").prop("disabled", true);
        } else {
            jQuery("#page_first, #page_prev").prop("disabled", false);
        }
    }

    jQuery("#viewerToolbar").on("mousedown", ".button", function() {
        !jQuery(this).is(":disabled") && jQuery(this).addClass("pressed");
    }).on("mouseup", ".button", function() {
        jQuery(this).removeClass("pressed");
    }).on("mouseenter", ".button", function() {
        !jQuery(this).is(":disabled") && jQuery(this).addClass("over");
    }).on("mouseleave", ".button", function() {
        jQuery(this).removeClass("pressed over");
    });

    jQuery("#viewerElements .menu .button").hover(function() {
        jQuery(this).addClass("over");
    }, function() {
        jQuery(this).removeClass("over");
    });

    jQuery("#export, #exportMenu").on({
        mouseenter: function() {
            var $exp = jQuery("#export"),
                $expM = jQuery("#exportMenu");

            $expM.removeClass("hidden");
            $expM.css({
                position: "fixed",
                top: $exp.position().top + $exp.height() + 1,
                left: $exp.position().left + 1
            });
        },
        mouseleave: function() {
            jQuery("#exportMenu").addClass("hidden");
        }
    });

    jQuery("#zoom_value_button, #zoomMenu").on({
        mouseenter: function() {
            var $zoomVal = jQuery("#zoom_value"),
                $zoomM = jQuery("#zoomMenu");

            $zoomM.removeClass("hidden");
            $zoomM.css({
                position: "fixed",
                top: $zoomVal.position().top + $zoomVal.outerHeight() + 3,
                left: $zoomVal.offset().left
            });
        },
        mouseleave: function() {
            jQuery("#zoomMenu").addClass("hidden");
        }
    });

    jQuery("#exportMenu").on("click", ".button", function() {
        var $selected = jQuery(this),
            outputFormat = $selected.data("val"),
            $expBtn = jQuery("#export");

        $expBtn.prop("disabled", true);
        jQuery("#exportMenu").addClass("hidden");

        report.export({
            outputFormat: outputFormat
        }).done(function (link) {
            $expBtn.prop("disabled", false);
            window.open(link.href);
        }).fail(failHandler);
    });

    jQuery("#zoomMenu").on("click", ".button", function() {
        var $selected = jQuery(this),
            scale = $selected.data("val");

        jQuery("#zoomMenu").addClass("hidden");

        report.scale(scale).run();
        updateScaleValue();
    });

    jQuery("#zoom_in").click(function () {
        report.scale(scale_index += 0.1).render();
        updateScaleValue();
    });

    jQuery("#zoom_out").click(function () {
        if (Math.round((scale_index - 0.1) * 100) > 0) {
            report.scale(scale_index -= 0.1).render();
            updateScaleValue();
        }
    });

    function updateScaleValue() {
        scale_index = report.scale();
        jQuery("#zoom_value").val(Math.round(scale_index * 100) + "%");
    }

    jQuery("#undo").on("click", function (evt) {
        report.undo().fail(failHandler);
    });

    jQuery("#undoAll").on("click", function (evt) {
        report.undoAll().fail(failHandler);
    });

    jQuery("#redo").on("click", function (evt) {
        report.redo().fail(failHandler);
    });

    jQuery("#page_first").on("click", function (evt) {
        currentPage = 1;
        report.pages(1)
                .run()
                .done(updatePaginationButtons)
                .fail(failHandler);
    });

    jQuery("#page_prev").on("click", function (evt) {
        report.pages(--currentPage)
                .run()
                .done(updatePaginationButtons)
                .fail(failHandler);
    });

    jQuery("#page_next").on("click", function (evt) {
        report.pages(++currentPage)
                .run()
                .done(updatePaginationButtons)
                .fail(failHandler);
    });

    jQuery("#page_last").on("click", function (evt) {
        currentPage = pagesNo;
        report.pages(pagesNo)
                .run()
                .done(updatePaginationButtons)
                .fail(failHandler);
    });

    jQuery("#page_current").on("change", function() {
        var intReg = /^\d+$/,
            val = this.value;

        if (intReg.exec(val)) {
            var parsed = parseInt(val);
            if (parsed > 0 && parsed <= pagesNo) {
                currentPage = parsed;
                report.pages(val)
                    .run()
                    .done(updatePaginationButtons)
                    .fail(failHandler)
            }
        }
    });
});
