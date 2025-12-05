export function customBtoa(data: any): string {
  if (typeof data !== "string") {
    data = JSON.stringify(data);
  }

  const replacedData: string = data.replace(/\\"/g, '\\\\\\"');

  return window.btoa(
    encodeURIComponent(replacedData).replace(/%([0-9A-F]{2})/g, (_, p1) =>
      String.fromCharCode(Number("0x" + p1)),
    ),
  );
}
