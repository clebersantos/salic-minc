// Generated by CoffeeScript 1.9.2

/*
@preserve jQuery.PrettyTextDiff 1.0.4
See https://github.com/arnab/jQuery.PrettyTextDiff/
 */

(function () {
  var $;

  $ = jQuery;

  $.fn.extend({
    prettyTextDiff: function (options) {

      var dmp, settings;
      settings = {
        originalContainer: ".original",
        changedContainer: ".changed",
        diffContainer: ".diff",
        cleanup: true,
        debug: false
      };
      settings = $.extend(settings, options);
      $.fn.prettyTextDiff.debug("Options: ", settings, settings);
      dmp = new diff_match_patch();
      
      return this.each(function () {
        var changed, diff_as_html, diffs, original;
        if (settings.originalContent && settings.changedContent) {
          original = $('<div />').html(settings.originalContent).text();
          changed = $('<div />').html(settings.changedContent).text();
        } else {
          original = $(settings.originalContainer, this).text();
          changed = $(settings.changedContainer, this).text();
        }
        $.fn.prettyTextDiff.debug("Original text found: ", original, settings);
        $.fn.prettyTextDiff.debug("Changed  text found: ", changed, settings);
        
        // original = $.fn.prettyTextDiff.createHtmlLines(original);
        // changed = $.fn.prettyTextDiff.createHtmlLines(changed);
    

        diffs = dmp.diff_main(original, changed);
   
        if (settings.cleanup) {
          dmp.diff_cleanupSemantic(diffs);
        }

        $.fn.prettyTextDiff.debug("Diffs: ", diffs, settings);
    
        var right = [], left = [];
        diffs.forEach(function(diff, i) {

          let text = $.fn.prettyTextDiff.createHTML(diff);

          switch (diff[0]) {
            case DIFF_INSERT:
            right[i] = text;
            left[i] = '';
            break;
            case DIFF_DELETE:
            right[i] = '';
            left[i] = text;
            break;
            case DIFF_EQUAL:
            right[i] = text;
            left[i] = text;
            break;
          }

        });

        // diff_as_html = $.map(diffs, function(diff) {
        //   return $.fn.prettyTextDiff.createHTML(diff);
        // });
       
        if (settings.originalContainer && settings.changedContainer) {
          $(settings.originalContainer,this).html(left.join(''));
          $(settings.changedContainer,this).html(right.join(''));
        }else {
          // $(settings.diffContainer, this).html(right.join(''));
          // $(settings.originalContainer,this).html(left.join(''));
        }

        return this;
      });
    }
  });

  $.fn.prettyTextDiff.debug = function (message, object, settings) {
    if (settings.debug) {
      return console.log(message, object);
    }
  };

  $.fn.prettyTextDiff.createHTML = function (diff) {
    var data, html, operation, pattern_amp, pattern_gt, pattern_lt, pattern_para, text;
    html = [];
    pattern_amp = /&/g;
    pattern_lt = /</g;
    pattern_gt = />/g;
    pattern_para = /\n/g;
    operation = diff[0], data = diff[1];

    text = data.replace(pattern_amp, '&amp;').replace(pattern_lt, '&lt;').replace(pattern_gt, '&gt;').replace(pattern_para, '<br>');
  
    switch (operation) {
      case DIFF_INSERT:
        return '<ins>' + text + '</ins>';
      case DIFF_DELETE:
        return '<del>' + text + '</del>';
      case DIFF_EQUAL:
        return '<span>' + text + '</span>';
    }
  };

  $.fn.prettyTextDiff.createHtmlLines = function(text, op, options) {
    var lines = text.split('\n');
    var y;
    for (y = 0; y < lines.length; y++) {
      if (lines[y].length === 0) {
        continue;
      }
      lines[y] = '<td>' + lines[y] + '</td>';
    }
    return lines.join('');
  };

  $.fn.prettyTextDiff.createLineDiffHtml = function(left, right, settings) {
    var dmp;
    var chars;
    var diffs;
    // if (assertArgumentsIsStrings(left, right)) {
      dmp = new diff_match_patch();
      var ignoreTrailingNewLines = (settings != 'undefined') && (settings.ignoreTrailingNewLines != 'undefined') && settings.ignoreTrailingNewLines;
      chars =  $.fn.prettyTextDiff.linesToChars(left, right, ignoreTrailingNewLines);
      diffs = dmp.diff_main(chars.chars1, chars.chars2, false);
      $.fn.prettyTextDiff.charsToLines(diffs, chars.lineArray, ignoreTrailingNewLines);
      return createHtmlFromDiffs(diffs, displayType.LINEDIFF, settings);
    // }
    return '';
  };

  $.fn.prettyTextDiff.linesToChars = function(text1, text2, ignoreTrailingNewLines) {
    var lineArray = [];
    var lineHash = {};
    lineArray[0] = '';

    function linesToCharsMunge(text) {
      var chars = '';
      var lineStart = 0;
      var lineEnd = -1;
      var lineArrayLength = lineArray.length;
      var hasNewLine = false;
      while (lineEnd < text.length - 1) {
        lineEnd = text.indexOf('\n', lineStart);
        hasNewLine = (lineEnd !== -1);
        if (!hasNewLine) {
          lineEnd = text.length - 1;
        }

        var line = text.substring(lineStart, lineEnd + ((ignoreTrailingNewLines && hasNewLine) ? 0 : 1));
        lineStart = lineEnd + 1;

        if (Object.prototype.hasOwnProperty.call(lineHash, line)) {
          chars += String.fromCharCode(lineHash[line]);
        } else {
          chars += String.fromCharCode(lineArrayLength);
          lineHash[line] = lineArrayLength;
          lineArray[lineArrayLength++] = line;
        }
      }
      return chars;
    }

    var chars1 = linesToCharsMunge(text1);
    var chars2 = linesToCharsMunge(text2);
    return {chars1: chars1, chars2: chars2, lineArray: lineArray};
  };

  $.fn.prettyTextDiff.charsToLines = function(diffs, lineArray, ignoreTrailingNewLines) {
    for (var x = 0; x < diffs.length; x++) {
      var chars = diffs[x][1];
      var text = [];
      for (var y = 0; y < chars.length; y++) {
        text[y] = lineArray[chars.charCodeAt(y)];
      }
      diffs[x][1] = text.join((ignoreTrailingNewLines) ? '\n' : '');
    }
    return diffs;
  };

}).call(this);