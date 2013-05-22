  private $authorName;
  private $authorEmail;

  public function setAuthorEmail($author_email) {
    $this->authorEmail = $author_email;
    return $this;
  }

  public function getAuthorEmail() {
    return $this->authorEmail;
  }

  public function setAuthorName($author_name) {
    $this->authorName = $author_name;
    return $this;
  }

  public function getAuthorName() {
    return $this->authorName;
  }

  public function getFullAuthor() {
    $author_name = $this->getAuthorName();
    if ($author_name === null) {
      return null;
    }

    $author_email = $this->getAuthorEmail();
    if ($author_email === null) {
      return null;
    }

    $full_author = sprintf('%s <%s>', $author_name, $author_email);

    // Because git is very picky about the author being in a valid format,
    // verify that we can parse it.
    $address = new PhutilEmailAddress($full_author);
    if (!$address->getDisplayName() || !$address->getAddress()) {
      return null;
    }

    return $full_author;
  }
  private function getEOL($patch_type) {

    // NOTE: Git always generates "\n" line endings, even under Windows, and
    // can not parse certain patches with "\r\n" line endings. SVN generates
    // patches with "\n" line endings on Mac or Linux and "\r\n" line endings
    // on Windows. (This EOL style is used only for patch metadata lines, not
    // for the actual patch content.)

    // (On Windows, Mercurial generates \n newlines for `--git` diffs, as it
    // must, but also \n newlines for unified diffs. We never need to deal with
    // these as we use Git format for Mercurial, so this case is currently
    // ignored.)

    switch ($patch_type) {
      case 'git':
        return "\n";
      case 'unified':
        return phutil_is_windows() ? "\r\n" : "\n";
      default:
        throw new Exception(
          "Unknown patch type '{$patch_type}'!");
    }
  }

      'tar tfO %s',
      $path);
        'tar xfO %s meta.json',
        $path);
      $author_name   = idx($meta_info, 'authorName');
      $author_email  = idx($meta_info, 'authorEmail');
      // this arc bundle was probably made before we started storing meta info
      $author        = null;
      'tar xfO %s changes.json',
      $path);
      'version'      => 5,
      'authorName'   => $this->getAuthorName(),
      'authorEmail'  => $this->getAuthorEmail(),
    $eol = $this->getEOL('unified');

      $hunk_changes = $this->buildHunkChanges($change->getHunks(), $eol);
      $result[] = $eol;
      $result[] = $eol;
      $result[] = '--- '.$old_path.$eol;
      $result[] = '+++ '.$cur_path.$eol;
    $diff = implode('', $result);
    $eol = $this->getEOL('git');

    $binary_sources = array();
    foreach ($changes as $change) {
      if (!$this->isGitBinaryChange($change)) {
        continue;
      }

      $type = $change->getType();
      if ($type == ArcanistDiffChangeType::TYPE_MOVE_AWAY ||
          $type == ArcanistDiffChangeType::TYPE_COPY_AWAY ||
          $type == ArcanistDiffChangeType::TYPE_MULTICOPY) {
        foreach ($change->getAwayPaths() as $path) {
          $binary_sources[$path] = $change;
        }
      }
    }

      // changes, so find one of them arbitrarily and turn it into a MOVE_HERE.
      $is_binary = $this->isGitBinaryChange($change);
        $old_binary = idx($binary_sources, $this->getCurrentPath($change));
        $change_body = $this->buildBinaryChange($change, $old_binary);
        $change_body = $this->buildHunkChanges($change->getHunks(), $eol);
      $result[] = "diff --git {$old_index} {$cur_index}".$eol;
        $result[] = "new file mode {$new_mode}".$eol;
          $type == ArcanistDiffChangeType::TYPE_COPY_AWAY ||
          $type == ArcanistDiffChangeType::TYPE_CHANGE) {
          $result[] = "old mode {$old_mode}".$eol;
          $result[] = "new mode {$new_mode}".$eol;
        $result[] = "copy from {$old_path}".$eol;
        $result[] = "copy to {$cur_path}".$eol;
        $result[] = "rename from {$old_path}".$eol;
        $result[] = "rename to {$cur_path}".$eol;
          $result[] = "deleted file mode {$old_mode}".$eol;
          $result[] = "--- {$old_target}".$eol;
          $result[] = "+++ {$cur_target}".$eol;
    $diff = implode('', $result).$eol;
  private function isGitBinaryChange(ArcanistDiffChange $change) {
    $file_type = $change->getFileType();
    return ($file_type == ArcanistDiffChangeType::FILE_BINARY ||
            $file_type == ArcanistDiffChangeType::FILE_IMAGE);
  }

    $lines = phutil_split_lines($base_hunk->getCorpus());
      $corpus = implode('', $corpus);
  private function buildHunkChanges(array $hunks, $eol) {
        $result[] = "@@ -{$o_head} +{$n_head} @@".$eol;

        $last = substr($corpus, -1);
        if ($last !== false && $last != "\r" && $last != "\n") {
          $result[] = $eol;
        }
    return implode('', $result);
  private function getBlob($phid, $name = null) {
    $console = PhutilConsole::getConsole();

      if ($name) {
        $console->writeErr("Downloading binary data for '%s'...\n", $name);
      } else {
        $console->writeErr("Downloading binary data...\n");
      }
  private function buildBinaryChange(ArcanistDiffChange $change, $old_binary) {
    $eol = $this->getEOL('git');

    // In Git, when we write out a binary file move or copy, we need the
    // original binary for the source and the current binary for the
    // destination.

    if ($old_binary) {
      if ($old_binary->getOriginalFileData() !== null) {
        $old_data = $old_binary->getOriginalFileData();
        $old_phid = null;
      } else {
        $old_data = null;
        $old_phid = $old_binary->getMetadata('old:binary-phid');
      }
    } else {
      $old_data = $change->getOriginalFileData();
      $old_phid = $change->getMetadata('old:binary-phid');
    }

    if ($old_data === null && $old_phid) {
      $name = basename($change->getOldPath());
      $old_data = $this->getBlob($old_phid, $name);
    }
    $old_length = strlen($old_data);

    if ($old_data === null) {
    $new_phid = $change->getMetadata('new:binary-phid');

    $new_data = null;
    if ($change->getCurrentFileData() !== null) {
      $new_data = $change->getCurrentFileData();
    } else if ($new_phid) {
      $name = basename($change->getCurrentPath());
      $new_data = $this->getBlob($new_phid, $name);
    }

    $new_length = strlen($new_data);

    if ($new_data === null) {
    $content[] = "index {$old_sha1}..{$new_sha1}".$eol;
    $content[] = "GIT binary patch".$eol;
    $content[] = "literal {$new_length}".$eol;
    $content[] = $this->emitBinaryDiffBody($new_data).$eol;
    $content[] = "literal {$old_length}".$eol;
    $content[] = $this->emitBinaryDiffBody($old_data).$eol;
    return implode('', $content);
    $eol = $this->getEOL('git');

      $buf .= $eol;