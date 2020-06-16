/*! uppie v1.1.3 | (c) silverwind | BSD license */
(function(root, m) {
    if (typeof define === "function" && define.amd) {
      define([], m);
    } else if (typeof module === "object" && module.exports) {
      module.exports = m();
    } else {
      root.Uppie = m();
    }
  })(typeof self !== "undefined" ? self : this, function() {
    "use strict";
    return function Uppie() {
      var defaultOpts = {
        name: "files[]",
      };
  
      return function(node, opts, cb) {
        if (typeof opts === "function") {
          cb = opts;
          opts = defaultOpts;
        } else {
          if (!opts) opts = {};
          if (!opts.name) opts.name = defaultOpts.name;
        }
  
        if (node instanceof NodeList) {
          [].slice.call(node).forEach(function(n) {
            watch(n, opts, cb);
          });
        } else {
          watch(node, opts, cb);
        }
      };
    };
  
    function watch(node, opts, cb) {
      if (node.tagName.toLowerCase() === "input" && node.type === "file") {
        node.addEventListener("change", function(event) {
          var t = event.target;
          if (t.files && t.files.length) {
            arrayApi(t, opts, cb.bind(null, event));
          } else if ("getFilesAndDirectories" in t) {
            newDirectoryApi(t, opts, cb.bind(null, event));
          } else {
            cb(event);
          }
        });
      } else {
        var stop = function(event) { event.preventDefault(); };
        node.addEventListener("dragover", stop);
        node.addEventListener("dragenter", stop);
        node.addEventListener("drop", function(event) {
          event.preventDefault();
          var dt = event.dataTransfer;
          if (dt.items && dt.items.length && "webkitGetAsEntry" in dt.items[0] && dt.items[0].webkitGetAsEntry()) {
            entriesApi(dt.items, opts, cb.bind(null, event));
          } else if ("getFilesAndDirectories" in dt) {
            newDirectoryApi(dt, opts, cb.bind(null, event));
          } else if (dt.files) {
            arrayApi(dt, opts, cb.bind(null, event));
          } else cb(event);
        });
      }
    }
  
    // API implemented in Firefox 42+ and Edge
    function newDirectoryApi(input, opts, cb) {
      var fd = new FormData(), files = [];
      var iterate = function(entries, path, resolve) {
        var promises = [];
        entries.forEach(function(entry) {
          promises.push(new Promise(function(resolve) {
            if ("getFilesAndDirectories" in entry) {
              entry.getFilesAndDirectories().then(function(entries) {
                iterate(entries, entry.path + "/", resolve);
              });
            } else {
              if (entry.name) {
                var p = (path + entry.name).replace(/^[/\\]/, "");
                fd.append(opts.name, entry, p);
                files.push(p);
              }
              resolve();
            }
          }));
        });
        Promise.all(promises).then(resolve);
      };
      input.getFilesAndDirectories().then(function(entries) {
        new Promise(function(resolve) {
          iterate(entries, "/", resolve);
        }).then(cb.bind(null, fd, files));
      });
    }
  
    // old prefixed API implemented in Chrome 11+ as well as array fallback
    function arrayApi(input, opts, cb) {
      var fd = new FormData(), files = [];
      [].slice.call(input.files).forEach(function(file) {
        fd.append(opts.name, file, file.webkitRelativePath || file.name);
        files.push(file.webkitRelativePath || file.name);
      });
      cb(fd, files);
    }
  
    function readEntries(entry, reader, oldEntries, cb) {
      var dirReader = reader || entry.createReader();
      dirReader.readEntries(function(entries) {
        var newEntries = oldEntries ? oldEntries.concat(entries) : entries;
        if (entries.length) {
          setTimeout(readEntries.bind(null, entry, dirReader, newEntries, cb), 0);
        } else {
          cb(newEntries);
        }
      });
    }
  
    // old drag and drop API implemented in Chrome 11+
    function entriesApi(items, opts, cb) {
      var fd = new FormData(), files = [], rootPromises = [];
  
      function readDirectory(entry, path, resolve) {
        if (!path) path = entry.name;
        readEntries(entry, 0, 0, function(entries) {
          var promises = [];
          entries.forEach(function(entry) {
            promises.push(new Promise(function(resolve) {
              if (entry.isFile) {
                entry.file(function(file) {
                  var p = path + "/" + file.name;
                  fd.append(opts.name, file, p);
                  files.push(p);
                  resolve();
                }, resolve.bind());
              } else readDirectory(entry, path + "/" + entry.name, resolve);
            }));
          });
          Promise.all(promises).then(resolve.bind());
        });
      }
  
      [].slice.call(items).forEach(function(entry) {
        entry = entry.webkitGetAsEntry();
        if (entry) {
          rootPromises.push(new Promise(function(resolve) {
            if (entry.isFile) {
              entry.file(function(file) {
                fd.append(opts.name, file, file.name);
                files.push(file.name);
                resolve();
              }, resolve.bind());
            } else if (entry.isDirectory) {
              readDirectory(entry, null, resolve);
            }
          }));
        }
      });
      Promise.all(rootPromises).then(cb.bind(null, fd, files));
    }
  });