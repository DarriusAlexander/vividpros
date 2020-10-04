export function getContactProperties() {
  return window.childFrameConnection.promise.then(child =>
    child.getContactProperties()
  );
}
