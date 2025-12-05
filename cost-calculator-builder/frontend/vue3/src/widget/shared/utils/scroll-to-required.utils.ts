import $ from "jquery";

export const scrollIntoRequired = (
  alias: string,
  repeaterIdx: number | undefined,
  calcId: number,
): void => {
  let query = `#ccb_app_${calcId} div[data-id="${alias}"]`;
  if (typeof repeaterIdx !== "undefined") {
    query += `[data-repeater="${repeaterIdx}"]`;
  }

  const offsetTop =
    $(query)
      .filter(function () {
        if (
          $(this)
            .parents(".ccb-horizontal, .ccb-vertical, .ccb-two-column")
            .css("display") !== "none"
        ) {
          return true;
        }
        return false;
      })
      .offset()?.top || 0;

  const offset = offsetTop - ($(window)?.scrollTop() || 0);
  if (offset > (window?.innerHeight || 0) || offset < 20) {
    $([document.documentElement, document.body]).animate(
      { scrollTop: offsetTop - 50 },
      500,
    );
  }
};
